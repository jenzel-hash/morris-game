// === API HANDLER ===
const API = {
  getState: (code) =>
    fetch(`api/state.php?code=${code}`, {
      credentials: 'same-origin'
    }).then(r => r.json()),

  saveState: (code, state) =>
    fetch('api/state.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify({ code, state })
    }).then(r => r.json()),

  resign: (code) =>
    fetch('api/resign.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify({ code })
    }).then(r => r.json())
};

// === GLOBAL VARS ===
const CODE = window.__ROOM_CODE__;
let YOU = null;
let ROOM = null;
let STATE = null;
let SELECTION = null;
let LAST_HASH = '';
let POLL_TIMER = null;

// === STONE CUSTOMIZATION FROM LOBBY ===
// Stone images are now loaded from the database via ROOM.players.W.stone_img and ROOM.players.B.stone_img
// Falls back to stoneColor palette if no custom image
let STONE_THEME = localStorage.getItem('stoneColor') || 'yellowred';


// Palette based on your underwater colors
const STONE_COLORS = {
  // Light = Water, Dark = Prussian Blue
  yellowred: {
    W: '#0c2b2aff', // Water
    B: '#1C4EA7'  // Prussian Blue
  },
  // Light = Sky Blue, Dark = Midnight Green
  blueblack: {
    W: '#0c2b2aff', // Sky Blue (Crayola)
    B: '#024D60'  // Midnight Green
  },
  // Light = Light Sea Green, Dark = Midnight Green
  greenwhite: {

    W: '#0c2b2aff', // Light Sea Green
    B: '#024D60'  // Midnight Green
  }
};


// === GAME BOARD POSITIONS ===
const positions = [
  'a1', 'a4', 'a7', 'b2', 'b4', 'b6', 'c3', 'c4', 'c5',
  'd1', 'd2', 'd3', 'd5', 'd6', 'd7', 'e3', 'e4', 'e5',
  'f2', 'f4', 'f6', 'g1', 'g4', 'g7'
];

const coords = {
  a1: [5, 5], a4: [5, 50], a7: [5, 95],
  d1: [50, 5], d7: [50, 95],
  g1: [95, 5], g4: [95, 50], g7: [95, 95],
  b2: [20, 20], b4: [20, 50], b6: [20, 80],
  d2: [50, 20], d6: [50, 80],
  f2: [80, 20], f4: [80, 50], f6: [80, 80],
  c3: [35, 35], c4: [35, 50], c5: [35, 65],
  d3: [50, 35], d5: [50, 65],
  e3: [65, 35], e4: [65, 50], e5: [65, 65]
};

const adj = {
  a1: ['a4', 'd1'], a4: ['a1', 'a7', 'b4'], a7: ['a4', 'd7'],
  b2: ['b4', 'd2'], b4: ['a4', 'b2', 'b6', 'c4'], b6: ['b4', 'd6'],
  c3: ['c4', 'd3'], c4: ['b4', 'c3', 'c5'], c5: ['c4', 'd5'],
  d1: ['a1', 'd2', 'g1'], d2: ['b2', 'd1', 'd3', 'f2'], d3: ['c3', 'd2', 'e3'],
  d5: ['c5', 'd6', 'e5'], d6: ['b6', 'd5', 'd7', 'f6'], d7: ['a7', 'd6', 'g7'],
  e3: ['d3', 'e4'], e4: ['e3', 'e5', 'f4'], e5: ['e4', 'd5'],
  f2: ['d2', 'f4'], f4: ['f2', 'f6', 'e4', 'g4'], f6: ['f4', 'd6'],
  g1: ['d1', 'g4'], g4: ['g1', 'g7', 'f4'], g7: ['g4', 'd7']
};

// All valid mills
const mills = [
  // Outer square
  ['a1', 'a4', 'a7'],
  ['d1', 'd2', 'd3'],
  ['g1', 'g4', 'g7'],
  ['d5', 'd6', 'd7'],
  ['a1', 'd1', 'g1'],
  ['a4', 'd4', 'g4'], // d4 does not exist, so skip
  ['a7', 'd7', 'g7'],

  // Middle square
  ['b2', 'b4', 'b6'],
  ['f2', 'f4', 'f6'],
  ['b2', 'd2', 'f2'],
  ['b4', 'd4', 'f4'], // d4 does not exist, so skip
  ['b6', 'd6', 'f6'],

  // Inner square
  ['c3', 'c4', 'c5'],
  ['e3', 'e4', 'e5'],
  ['c3', 'd3', 'e3'],
  ['c4', 'd4', 'e4'], // d4 does not exist, so skip
  ['c5', 'd5', 'e5'],

  // Cross-square verticals
  ['a1', 'b2', 'c3'],
  ['a4', 'b4', 'c4'],
  ['a7', 'b6', 'c5'],
  ['d1', 'd2', 'd3'],
  ['d5', 'd6', 'd7'],
  ['g1', 'f2', 'e3'],
  ['g4', 'f4', 'e4'],
  ['g7', 'f6', 'e5'],

  // Cross-square horizontals
  ['b2', 'c3', 'd3'],
  ['b4', 'c4', 'd4'], // d4 does not exist, so skip
  ['b6', 'c5', 'd5'],
  ['f2', 'e3', 'd3'],
  ['f4', 'e4', 'd4'], // d4 does not exist, so skip
  ['f6', 'e5', 'd5'],

  // Center verticals
  ['d1', 'd2', 'd3'],
  ['d5', 'd6', 'd7'],

  // Center horizontals
  ['c4', 'd4', 'e4'], // d4 does not exist, so skip
];

// === DRAW BOARD ===
function drawBoard(container) {
  container.innerHTML = '';
  const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
  svg.setAttribute('viewBox', '0 0 100 100');
  svg.setAttribute('preserveAspectRatio', 'xMidYMid meet');

  // Define patterns for per-player custom stone images
  const defs = document.createElementNS(svg.namespaceURI, 'defs');

  // Pattern for White/Player 1 stone image (owner-based)
  const patternW = document.createElementNS(svg.namespaceURI, 'pattern');
  patternW.setAttribute('id', 'stonePattern_W');
  patternW.setAttribute('patternUnits', 'objectBoundingBox');
  patternW.setAttribute('width', '1');
  patternW.setAttribute('height', '1');
  const imgW = document.createElementNS(svg.namespaceURI, 'image');
  imgW.setAttribute('width', '10');
  imgW.setAttribute('height', '10');
  imgW.setAttribute('preserveAspectRatio', 'xMidYMid slice');
  patternW.appendChild(imgW);
  defs.appendChild(patternW);

  // Pattern for Black/Player 2 stone image (owner-based)
  const patternB = document.createElementNS(svg.namespaceURI, 'pattern');
  patternB.setAttribute('id', 'stonePattern_B');
  patternB.setAttribute('patternUnits', 'objectBoundingBox');
  patternB.setAttribute('width', '1');
  patternB.setAttribute('height', '1');
  const imgB = document.createElementNS(svg.namespaceURI, 'image');
  imgB.setAttribute('width', '10');
  imgB.setAttribute('height', '10');
  imgB.setAttribute('preserveAspectRatio', 'xMidYMid slice');
  patternB.appendChild(imgB);
  defs.appendChild(patternB);

  svg.appendChild(defs);

  const squares = [6, 20, 34];
  squares.forEach(o => {
    const r = 100 - o;
    const rect = document.createElementNS(svg.namespaceURI, 'rect');
    rect.setAttribute('x', o);
    rect.setAttribute('y', o);
    rect.setAttribute('width', r - o);
    rect.setAttribute('height', r - o);
    rect.setAttribute('fill', 'none');
    rect.setAttribute('class', 'line');
    rect.setAttribute('stroke', 'var(--line)');
    rect.setAttribute('stroke-width', '0.8');
    rect.setAttribute('stroke-linecap', 'round');
    rect.setAttribute('stroke-linejoin', 'round');
    rect.setAttribute('opacity', '0.9');

    svg.appendChild(rect);
  });

  const lines = [['a4', 'c4'], ['e4', 'g4'], ['d1', 'd3'], ['d5', 'd7']];
  lines.forEach(([p1, p2]) => {
    const [x1, y1] = coords[p1];
    const [x2, y2] = coords[p2];
    const l = document.createElementNS(svg.namespaceURI, 'line');
    l.setAttribute('x1', x1);
    l.setAttribute('y1', y1);
    l.setAttribute('x2', x2);
    l.setAttribute('y2', y2);
    l.setAttribute('class', 'line');
    l.setAttribute('stroke', 'var(--line)');
    l.setAttribute('stroke-width', '0.7');
    l.setAttribute('stroke-linecap', 'round');
    l.setAttribute('opacity', '0.85');

    svg.appendChild(l);
  });

  positions.forEach(p => {
    const [x, y] = coords[p];
    const c = document.createElementNS(svg.namespaceURI, 'circle');
    c.setAttribute('cx', x);
    c.setAttribute('cy', y);
    c.setAttribute('r', 4.9);
    c.dataset.pos = p;
    c.classList.add('point');
    c.setAttribute('fill', 'rgba(0,0,0,0.35)');
    c.setAttribute('stroke', '#024D60');
    c.setAttribute('stroke-width', '0.35');
    c.setAttribute('opacity', '0.9');
    svg.appendChild(c);
  });

  container.appendChild(svg);
}

// === NORMALIZATION HELPERS ===
// Simplified and improved normalization helper for clearer symbol mapping
function normalizeValue(v) {
  if (v === null || v === undefined) return null;
  if (typeof v === 'string') {
    const s = v.trim().toUpperCase();
    if (s === 'W' || s === 'WHITE' || s === 'YELLOW' || s === 'LIGHT' || s === 'BEIGE') return 'W';
    if (s === 'B' || s === 'BLACK' || s === 'RED' || s === 'DARK' || s === 'BLUE' || s === 'GREEN') return 'B';
    // Basic hex color heuristic for light/dark with logging for edge cases
    if (s.startsWith('#') && s.length === 7) {
      const r = parseInt(s.slice(1, 3), 16);
      const g = parseInt(s.slice(3, 5), 16);
      const b = parseInt(s.slice(5, 7), 16);
      const avg = (r + g + b) / 3;
      const result = avg > 127 ? 'W' : 'B';
      return result;
    }
    return null;
  }
  if (typeof v === 'object') {
    if (v.player && (v.player === 'W' || v.player === 'B')) return v.player;
    if (v.color) {
      const s = String(v.color).toLowerCase();
      if (s.includes('white') || s.includes('light') || s.includes('yellow')) return 'W';
      if (s.includes('black') || s.includes('red') || s.includes('dark') || s.includes('blue')) return 'B';
    }
    if (v.theme && (v.theme === 'W' || v.theme === 'B')) return v.theme;
  }
  return null;
}

// Updated to handle both object and array formats from server
function normalizeBoard(rawBoard) {
  const board = {};
  if (!rawBoard) return board;
  if (Array.isArray(rawBoard)) {
    // Assume array is in order of 'positions'
    positions.forEach((p, i) => {
      const val = rawBoard[i] !== undefined ? rawBoard[i] : null;
      board[p] = normalizeValue(val);
    });
  } else if (typeof rawBoard === 'object') {
    // Original object handling
    for (const p of positions) {
      board[p] = normalizeValue(rawBoard[p]);
    }
  }
  return board;
}

// === HELPERS ===
// Enhanced mill detection with debug logs for diagnosis
function isMillAt(board, pos, sym) {
  // Normalize board and symbol
  const normBoard = normalizeBoard(board);
  const symNorm = (sym === 'W' || sym === 'B') ? sym : normalizeValue(sym);
  if (!symNorm) return false;

  for (const line of mills) {
    // Check if the position is part of the mill
    if (line.includes(pos)) {
      // Ensure all positions in the mill are occupied by the same symbol
      const matched = line.every(p => normBoard[p] === symNorm);

      // Ensure the positions in the mill are connected by valid lines
      if (matched) {
        let connected = true;
        for (let i = 0; i < line.length - 1; i++) {
          const p1 = line[i];
          const p2 = line[i + 1];
          if (!adj[p1] || !adj[p1].includes(p2)) {
            connected = false; // Not connected by valid lines
            break;
          }
        }
        if (connected) {
          console.debug(`Mill detected for ${symNorm} at positions: ${line.join(', ')}`);
          return true; // Mill detected
        }
      }
    }
  }

  console.debug(`No mill for ${symNorm} at position ${pos}. Board values: ${JSON.stringify(normBoard)}`);
  return false; // No mill detected
}

function piecesCount(board, sym) {
  const s = (sym === 'W' || sym === 'B') ? sym : normalizeValue(sym);
  if (!s) return 0;
  return positions.reduce((acc, p) => acc + (board[p] === s ? 1 : 0), 0);
}

function computePhase(state) {
  const { W, B } = state.piecesPlaced;
  return (W < 9 || B < 9) ? 'placing' : 'moving';
}

// === RENDER ===
function styleStoneCircle(circleElem, player) {
  // Use per-player custom image from ROOM data if available, else default to color
  const theme = STONE_COLORS[localStorage.getItem('stoneColor') || 'yellowred'] || STONE_COLORS.yellowred;
  if (ROOM && ROOM.players && ROOM.players[player] && ROOM.players[player].stone_img) {
    circleElem.setAttribute('fill', `url(#stonePattern_${player})`);
  } else {
    // fallback to color if no custom image
    circleElem.setAttribute('fill', theme[player]);
    circleElem.setAttribute('opacity', '0.96');
  }
  circleElem.setAttribute('stroke', '#F5F7FF');
  circleElem.setAttribute('stroke-width', '0.5');
}


function render() {
  if (!STATE) return;
  const wrap = document.getElementById('boardWrap');
  if (!wrap.querySelector('svg')) drawBoard(wrap);

  const svg = wrap.querySelector('svg');

  // Update patterns if ROOM is available (owner-based images)
  if (ROOM) {
    const imgW = svg.querySelector('#stonePattern_W image');
    if (imgW && ROOM.players.W.stone_img) imgW.setAttributeNS('http://www.w3.org/1999/xlink', 'href', ROOM.players.W.stone_img);
    const imgB = svg.querySelector('#stonePattern_B image');
    if (imgB && ROOM.players.B.stone_img) imgB.setAttributeNS('http://www.w3.org/1999/xlink', 'href', ROOM.players.B.stone_img);
  }

  const board = normalizeBoard(STATE.board);

  svg.querySelectorAll('circle.point').forEach(c => {
    const p = c.dataset.pos;
    const v = board[p];
    c.classList.remove('red', 'white', 'sel', 'empty');

    if (v === null) {
      c.classList.add('empty');
      c.removeAttribute('fill');
      c.removeAttribute('stroke');
    } else if (v === 'B' || v === 'W') {
      styleStoneCircle(c, v);
    }

    if (SELECTION === p) c.classList.add('sel');
  });

  const players = document.getElementById('players');
  const turn = document.getElementById('turn');
  const phase = document.getElementById('phase');
  const hint = document.getElementById('hint');


  // Get profile images (default if missing)
  const imgW = ROOM?.players?.W?.profile_img || 'assets/profile whale.jpg';
  const imgB = ROOM?.players?.B?.profile_img || 'assets/profile whale.jpg';
  const nameW = ROOM?.players?.W?.name || ROOM?.players?.W || '-';
  const nameB = ROOM?.players?.B?.name || ROOM?.players?.B || '-';

  players.innerHTML =
    `<span class="badge ${YOU === 'W' ? 'me' : ''}" style="display:inline-flex;align-items:center;gap:6px;">
      <img src="${escapeHtml(imgW)}" alt="W" style="width:28px;height:28px;border-radius:50%;border:2px solid #1e09d8ff;object-fit:cover;"> W: ${escapeHtml(nameW)}
    </span> ` +
    `<span class="badge ${YOU === 'B' ? 'me' : ''}" style="display:inline-flex;align-items:center;gap:6px;">
      <img src="${escapeHtml(imgB)}" alt="B" style="width:28px;height:28px;border-radius:50%;border:2px solid #1b48aaff;object-fit:cover;"> B: ${escapeHtml(nameB)}
    </span>`;

  turn.innerHTML = STATE.winner
    ? `<span class="badge" style="color:#FFD700;"><i class="fa-solid fa-trophy" style="margin-right:6px;"></i>Winner: ${escapeHtml(STATE.winner === 'W' ? nameW : nameB)}</span>`
    : `<span class="badge ${STATE.turn === 'W' ? 'turnW' : 'turnB'}">Turn: ${escapeHtml(STATE.turn === 'W' ? nameW : nameB)}</span>`;

  STATE.phase = computePhase(STATE);
  phase.innerHTML = `<span class="badge">Phase: ${escapeHtml(STATE.phase)}</span>`;

  if (STATE.removalPending && STATE.turn === YOU) {
    hint.textContent = 'You formed a mill! Remove one opponent piece.';
  } else if (STATE.removalPending && STATE.turn !== YOU) {
    hint.textContent = 'Opponent formed a mill! Please wait...';
  } else if (!STATE.winner && STATE.turn === YOU) {
    hint.textContent = 'Your move';
  } else if (!STATE.winner) {
    hint.textContent = 'Waiting for opponent...';
  } else {
    hint.textContent = '';
  }

  const countW = piecesCount(board, 'W');
  const countB = piecesCount(board, 'B');
  const remW = Math.max(0, 9 - (STATE.piecesPlaced?.W || 0));
  const remB = Math.max(0, 9 - (STATE.piecesPlaced?.B || 0));

  const stoneInfo = document.getElementById('stoneInfo');
  stoneInfo.innerHTML = `W Stones on board: ${countW} | Remaining to place: ${remW}<br>B Stones on board: ${countB} | Remaining to place: ${remB}`;

  if (STATE && STATE.winner) {
    const modal = document.getElementById('endModal');
    const title = document.getElementById('resultTitle');
    const msg = document.getElementById('resultMsg');
    const leaveBtn = document.getElementById('leaveBtn');
    modal.classList.remove('hidden');
    if (STATE.resigned && STATE.resigned_by) {
      if (STATE.winner === YOU) {
        title.textContent = 'You Win!';
        msg.textContent = 'Your opponent resigned. You win.';
      } else {
        title.textContent = 'You Lose';
        msg.textContent = 'Your opponent won.';
      }
    } else {
      title.textContent = (STATE.winner === YOU) ? 'You Win!' : 'You Lose';
      msg.textContent = (STATE.winner === YOU) ? 'Congratulations!' : 'Better luck next time.';
    }
    leaveBtn.onclick = () => { window.location.href = 'lobby.php'; };
  }
}

// simple HTML escape to avoid showing [object Object] in names
function escapeHtml(s) {
  return String(s || '').replace(/[&<>"']/g, function (m) { return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m]; });
}

// === POLLING ===
async function poll() {
  try {
    const data = await API.getState(CODE);
    if (!data || !data.ok) return;
    ROOM = data;
    if (YOU === null) YOU = data.you;
    // Normalize incoming state board immediately
    if (data.state) {
      // Build a normalized state copy to use in client
      const normalizedState = Object.assign({}, data.state);
      normalizedState.board = normalizeBoard(data.state.board || {});
      // ensure piecesPlaced exists
      normalizedState.piecesPlaced = normalizedState.piecesPlaced || { W: 0, B: 0 };
      if (!STATE) {
        STATE = normalizedState;
      } else {
        const newHash = JSON.stringify(normalizedState);
        if (newHash !== LAST_HASH) STATE = normalizedState;
      }
      LAST_HASH = JSON.stringify(STATE);
    }
    render();
  } catch (err) {
    console.error('Poll error', err);
  }
}

// === MOVE LOGIC ===
function canRemove(boardRaw, target, opp) {
  const board = normalizeBoard(boardRaw);
  if (board[target] !== opp) return false; // Ensure the target belongs to the opponent

  const oppPositions = positions.filter(p => board[p] === opp);
  const inMill = p => mills.some(line => line.includes(p) && line.every(q => board[q] === opp));

  // Check if all opponent stones are in mills
  const allInMill = oppPositions.every(p => inMill(p));

  // Allow removal of stones in a mill if all opponent stones are in mills
  if (allInMill || !inMill(target)) {
    console.debug(`Can remove ${target}. All opponent stones in mills: ${allInMill}`);
    return true; // Stone can be removed
  }

  console.debug(`Cannot remove ${target} because it is in a mill and not all opponent stones are in mills.`);
  return false; // Stone cannot be removed
}
function legalMove(from, to, sym) {
  const board = normalizeBoard(STATE.board);
  if (STATE.phase === 'placing') return board[to] === null;
  if (board[from] !== sym || board[to] !== null) return false;
  const count = piecesCount(board, sym);
  return count <= 3 || (adj[from] && adj[from].includes(to));
}

async function doSave() {
  const safeState = JSON.parse(JSON.stringify(STATE));
  const safeBoard = {};
  for (const p of positions) {
    const val = normalizeValue(STATE.board && STATE.board[p] !== undefined ? STATE.board[p] : null);
    safeBoard[p] = val;
  }
  safeState.board = safeBoard;
  LAST_HASH = JSON.stringify(safeState);
  await API.saveState(CODE, safeState);
}

function winnerCheck() {
  const opp = STATE.turn === 'W' ? 'B' : 'W';
  const board = normalizeBoard(STATE.board);

  // Ensure both players have placed all 9 stones before checking for a winner
  if (STATE.phase === 'placing') {
    return null; // No winner can be determined during the placing phase
  }

  const oppCount = piecesCount(board, opp);

  // If opponent has 2 or fewer stones, you win
  if (oppCount <= 2) {
    STATE.winner = STATE.turn;
    return STATE.winner;
  }

  // If opponent has no legal moves, you win
  if (STATE.phase === 'moving') {
    const oppPositions = positions.filter(p => board[p] === opp);
    let hasMoves = false;
    for (const pos of oppPositions) {
      const count = piecesCount(board, opp);
      // If opponent has 3 or fewer stones, they can fly anywhere
      if (count <= 3) {
        hasMoves = positions.some(p => board[p] === null);
        break;
      }
      const neighbours = adj[pos] || [];
      for (const n of neighbours) {
        if (board[n] === null) {
          hasMoves = true;
          break;
        }
      }
      if (hasMoves) break;
    }
    if (!hasMoves) {
      STATE.winner = STATE.turn;
      return STATE.winner;
    }
  }

  return null;
}
function playStoneSound() {
  if (!SOUND_ENABLED) return;
  const audio = document.getElementById('stone-sound');
  if (!audio) return;
  audio.currentTime = 0;
  audio.play().catch(() => { });
}


let SOUND_ENABLED = localStorage.getItem('soundEnabled') !== 'false';


// === EVENT BINDINGS ===
function bindEvents() {
  const wrapElem = document.getElementById('boardWrap');
  // Use event delegation on the container so clicks work even if the SVG
  // is re-created after listeners are attached.
  wrapElem.addEventListener('click', async e => {
    try {
      if (!YOU || STATE.winner) return;
      if (STATE.turn !== YOU) return;
      const c = e.target.closest('circle.point');
      if (!c) return;
      const pos = c.dataset.pos;
      const me = YOU, opp = YOU === 'W' ? 'B' : 'W';

      if (STATE.removalPending) {
        if (canRemove(STATE.board, pos, opp)) {
          STATE.board[pos] = null;
          STATE.removalPending = false;

          // Check if opponent now has only 2 stones, declare winner only in "moving" phase
          const boardNow = normalizeBoard(STATE.board);
          const oppCount = piecesCount(boardNow, opp);
          if (STATE.phase === 'moving' && oppCount <= 2) {
            STATE.winner = YOU;
          } else {
            STATE.turn = opp;
            winnerCheck();
          }
          await doSave();
          render();
        } else {
          console.log('Invalid removal attempt at', pos);
        }
        return;
      }

      if (STATE.phase === 'placing') {
        const boardNormalized = normalizeBoard(STATE.board);
        if (boardNormalized[pos] === null) {
          // Place the stone
          STATE.board[pos] = me;
          STATE.piecesPlaced = STATE.piecesPlaced || { W: 0, B: 0 };
          STATE.piecesPlaced[me] = (STATE.piecesPlaced[me] || 0) + 1;

          playStoneSound();

          const updatedBoard = normalizeBoard(STATE.board);
          // Check for mill
          if (isMillAt(updatedBoard, pos, me)) {
            STATE.removalPending = true;
          } else {
            STATE.turn = opp;
          }
          await doSave();
          render();
        }
        return;
      }

      // Moving/Flying phase logic remains unchanged
      if (!SELECTION) {
        const boardNormalized = normalizeBoard(STATE.board);
        if (boardNormalized[pos] === me) {
          SELECTION = pos;
          render();
          return;
        }
        return;
      }

      if (normalizeValue(STATE.board[pos]) === me) {
        SELECTION = pos;
        render();
        return;
      }

      const boardNormalized = normalizeBoard(STATE.board);
      if (legalMove(SELECTION, pos, me)) {
        STATE.board[SELECTION] = null;
        STATE.board[pos] = me;

        playStoneSound();

        SELECTION = null;

        const updatedBoard = normalizeBoard(STATE.board);
        if (isMillAt(updatedBoard, pos, me)) {
          STATE.removalPending = true;
        } else {
          STATE.turn = opp;
        }
        winnerCheck();
        await doSave();
        render();
      }
    } catch (err) {
      console.error('Click handler error', err);
    }
  });

  document.getElementById('resign').onclick = async () => {
    if (!confirm('Are you sure you want to resign this match?')) return;
    try {
      const res = await API.resign(CODE);
      if (res.ok) {
        alert('You have resigned. Returning to lobby...');
        window.location.href = 'lobby.php';
      } else {
        alert('Failed to resign: ' + (res.error || 'Unknown error'));
      }
    } catch (err) {
      alert('Failed to resign (network): ' + err.message);
    }
  };
}

// === INIT ===
(async function init() {
  await poll();
  if (!ROOM?.ok) {
    alert('Room not found or not registered. Go back to Lobby.');
    location.href = 'lobby.php';
    return;
  }

  // Stone images are now stored in the database, no need for localStorage assignment

  bindEvents();
  POLL_TIMER = setInterval(poll, 1200);

  // Sound toggle setup
  const soundBtn = document.getElementById('soundToggle');
  if (soundBtn) {
    // initial label based on saved preference
    soundBtn.textContent = SOUND_ENABLED ? 'Sound: On' : 'Sound: Off';

    soundBtn.addEventListener('click', () => {
      SOUND_ENABLED = !SOUND_ENABLED;
      localStorage.setItem('soundEnabled', SOUND_ENABLED ? 'true' : 'false');
      soundBtn.textContent = SOUND_ENABLED ? 'Sound: On' : 'Sound: Off';
    });
  }
})();