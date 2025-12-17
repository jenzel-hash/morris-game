USE morris_db;

-- Add profile_img column to players table
ALTER TABLE players ADD COLUMN profile_img VARCHAR(255) DEFAULT 'assets/whale.jpg';

-- Add stone_img column to players table
ALTER TABLE players ADD COLUMN stone_img VARCHAR(255) NULL;
