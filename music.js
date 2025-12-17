(function () {
  // Control site background music more robustly.
  // Find audio elements that are used as global/bg music on pages.
  const candidates = [];
  const byId = ['global-music', 'lobby-music'];
  byId.forEach(id => {
    const a = document.getElementById(id);
    if (a && a.tagName && a.tagName.toLowerCase() === 'audio') candidates.push(a);
  });
  // also include any audio explicitly marked with data-site-music
  document.querySelectorAll('audio[data-site-music]').forEach(a => candidates.push(a));
  // fallback: include the first looping audio element on the page
  if (candidates.length === 0) {
    const loopAudio = Array.from(document.querySelectorAll('audio')).find(a => a.hasAttribute('loop'));
    if (loopAudio) candidates.push(loopAudio);
  }

  if (candidates.length === 0) return; // nothing to control on this page

  const audios = Array.from(new Set(candidates)); // unique
  const toggle = document.getElementById('musicToggle'); // optional per-page button

  let musicEnabled = localStorage.getItem('globalMusic') !== 'off';

  function sync() {
    audios.forEach(audio => {
      try {
        audio.volume = 0.3;
        if (musicEnabled) {
          audio.play().catch(() => { });
        } else {
          audio.pause();
          audio.currentTime = 0;
        }
      } catch (e) {
        // ignore playback errors
      }
    });
    if (toggle) {
      toggle.textContent = musicEnabled ? 'Music: On' : 'Music: Off';
    }
  }

  // Pause music when the user navigates away by clicking a normal link
  function onLinkClick(e) {
    const a = e.target.closest && e.target.closest('a');
    if (!a) return;
    const href = a.getAttribute('href');
    if (!href) return;
    // If link is same-page anchor or javascript, ignore
    if (href.startsWith('#') || href.startsWith('javascript:')) return;
    // Otherwise pause audios to avoid bleeding across navigation
    audios.forEach(aud => {
      try { aud.pause(); aud.currentTime = 0; } catch (e) { }
    });
  }

  // Also pause on beforeunload as a fallback
  window.addEventListener('beforeunload', () => {
    audios.forEach(aud => {
      try { aud.pause(); aud.currentTime = 0; } catch (e) { }
    });
  });

  document.addEventListener('click', function startOnce() {
    if (musicEnabled) {
      audios.forEach(audio => audio.play().catch(() => { }));
    }
    document.removeEventListener('click', startOnce);
  });

  document.addEventListener('click', onLinkClick, true);

  if (toggle) {
    toggle.addEventListener('click', () => {
      musicEnabled = !musicEnabled;
      localStorage.setItem('globalMusic', musicEnabled ? 'on' : 'off');
      sync();
    });
  }

  // initialize
  sync();
})();
