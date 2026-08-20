
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS whats_left;
CREATE DATABASE whats_left CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE whats_left;

CREATE TABLE users (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name          VARCHAR(100) NOT NULL,
    last_name           VARCHAR(100) NOT NULL,
    email               VARCHAR(150) NOT NULL UNIQUE,
    password_hash       VARCHAR(255) NOT NULL,
    dob                 DATE NOT NULL,
    looking_for         ENUM('Male', 'Female', 'Everyone') NOT NULL,
    location            VARCHAR(150) DEFAULT NULL,
    description         TEXT DEFAULT NULL,
    is_matched          TINYINT(1) NOT NULL DEFAULT 0,
    current_match_id    INT UNSIGNED DEFAULT NULL,
    skip_used           TINYINT(1) NOT NULL DEFAULT 0,
    undo_used           TINYINT(1) NOT NULL DEFAULT 0,
    dislike_undo_used   TINYINT(1) NOT NULL DEFAULT 0,
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


CREATE TABLE user_photos (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED NOT NULL,
    photo_path   VARCHAR(255) NOT NULL,
    sort_order   INT UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT fk_user_photos_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE user_hobbies (
    id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id  INT UNSIGNED NOT NULL,
    hobby    VARCHAR(50) NOT NULL,
    CONSTRAINT fk_user_hobbies_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE user_personality (
    user_id                 INT UNSIGNED PRIMARY KEY,
    introvert_extrovert     TINYINT UNSIGNED NOT NULL DEFAULT 50,
    analytical_creative     TINYINT UNSIGNED NOT NULL DEFAULT 50,
    tech_savvy               TINYINT UNSIGNED NOT NULL DEFAULT 50,
    sporty                  TINYINT UNSIGNED NOT NULL DEFAULT 50,
    CONSTRAINT fk_user_personality_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE swipes (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    swiper_id      INT UNSIGNED NOT NULL,
    target_id      INT UNSIGNED NOT NULL,
    action         ENUM('like', 'dislike', 'skip') NOT NULL,
    reason_short   VARCHAR(150) DEFAULT NULL,
    reason_long    TEXT DEFAULT NULL,
    undone         TINYINT(1) NOT NULL DEFAULT 0,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_swipes_swiper FOREIGN KEY (swiper_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_swipes_target FOREIGN KEY (target_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE matches (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user1_id            INT UNSIGNED NOT NULL,
    user2_id            INT UNSIGNED NOT NULL,
    status              ENUM('active', 'ended') NOT NULL DEFAULT 'active',
    matched_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ended_at            DATETIME DEFAULT NULL,
    ended_by            INT UNSIGNED DEFAULT NULL,
    end_reason_short    VARCHAR(150) DEFAULT NULL,
    end_reason_long     TEXT DEFAULT NULL,
    undo_of_match       TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT fk_matches_user1 FOREIGN KEY (user1_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_matches_user2 FOREIGN KEY (user2_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_matches_ended_by FOREIGN KEY (ended_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

ALTER TABLE users
    ADD CONSTRAINT fk_users_current_match FOREIGN KEY (current_match_id) REFERENCES matches(id) ON DELETE SET NULL;

CREATE TABLE messages (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    match_id     INT UNSIGNED NOT NULL,
    sender_id    INT UNSIGNED NOT NULL,
    message      TEXT NOT NULL,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_messages_match FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    CONSTRAINT fk_messages_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE VIEW leaderboard_view AS
SELECT
    u.id,
    u.first_name,
    u.last_name,
    (
        SELECT COUNT(*) FROM matches m
        WHERE (m.user1_id = u.id OR m.user2_id = u.id) AND m.status = 'ended'
    ) AS number_of_unmatches,
    (
        SELECT COUNT(*) FROM swipes s
        WHERE s.swiper_id = u.id AND s.action = 'like'
    ) AS number_of_likes,
    (
        SELECT COUNT(*) FROM swipes s
        WHERE s.swiper_id = u.id AND s.action = 'dislike'
    ) AS number_of_dislikes,
    (
        SELECT m2.end_reason_long FROM matches m2
        WHERE (m2.user1_id = u.id OR m2.user2_id = u.id) AND m2.status = 'ended'
        ORDER BY m2.ended_at DESC
        LIMIT 1
    ) AS reason_for_most_recent_unmatch
FROM users u;


INSERT INTO users (id, first_name, last_name, email, password_hash, dob, looking_for, location, description, is_matched, current_match_id, skip_used, undo_used, dislike_undo_used) VALUES
(5, 'Pieter', 'Naidoo', 'pieter.naidoo5@example.com', '$2b$10$Oor2oh/guA7RuKLKPJMuZuRsFCo0LWGfYNWJtFtSkyN9ArURego9C', '1999-01-25', 'Male', 'Hatfield', 'Currently learning the guitar badly. Send help, or snacks.', 0, NULL, 0, 0, 0),
(6, 'Farah', 'Van Wyk', 'farah.vanwyk6@example.com', '$2b$10$Oor2oh/guA7RuKLKPJMuZuRsFCo0LWGfYNWJtFtSkyN9ArURego9C', '1996-06-20', 'Female', 'Pretoria', 'Trying every coffee shop in the city, one flat white at a time.', 0, NULL, 0, 0, 0),
(7, 'Sipho', 'Khumalo', 'sipho.khumalo7@example.com', '$2b$10$Oor2oh/guA7RuKLKPJMuZuRsFCo0LWGfYNWJtFtSkyN9ArURego9C', '2001-12-03', 'Male', 'Midrand', 'Will talk your ear off about rugby. Also a decent cook.', 0, NULL, 0, 0, 0),
(8, 'Xoli', 'Mahlangu', 'xoli.mahlangu8@example.com', '$2b$10$Oor2oh/guA7RuKLKPJMuZuRsFCo0LWGfYNWJtFtSkyN9ArURego9C', '1996-03-12', 'Female', 'Midrand', 'Will talk your ear off about rugby. Also a decent cook.', 0, NULL, 0, 0, 0),
(9, 'Nathi', 'Pretorius', 'nathi.pretorius9@example.com', '$2b$10$Oor2oh/guA7RuKLKPJMuZuRsFCo0LWGfYNWJtFtSkyN9ArURego9C', '2000-11-11', 'Male', 'Midrand', 'Trail runs on Saturdays, terrible at board games, great at making toast.', 0, NULL, 0, 0, 0),
(10, 'Kabelo', 'Bekker', 'kabelo.bekker10@example.com', '$2b$10$Oor2oh/guA7RuKLKPJMuZuRsFCo0LWGfYNWJtFtSkyN9ArURego9C', '1992-07-21', 'Female', 'Johannesburg', 'Will talk your ear off about rugby. Also a decent cook.', 0, NULL, 0, 0, 0),
(11, 'Riya', 'Sithole', 'riya.sithole11@example.com', '$2b$10$Oor2oh/guA7RuKLKPJMuZuRsFCo0LWGfYNWJtFtSkyN9ArURego9C', '1995-06-08', 'Male', 'Silverton', 'Trying every coffee shop in the city, one flat white at a time.', 0, NULL, 0, 0, 0),
(12, 'Liam', 'Joubert', 'liam.joubert12@example.com', '$2b$10$Oor2oh/guA7RuKLKPJMuZuRsFCo0LWGfYNWJtFtSkyN9ArURego9C', '1994-10-03', 'Female', 'Hatfield', 'Reads more than is socially acceptable. Ask me for recommendations.', 0, NULL, 0, 0, 0),
(13, 'Olivia', 'Naidoo', 'olivia.naidoo13@example.com', '$2b$10$Oor2oh/guA7RuKLKPJMuZuRsFCo0LWGfYNWJtFtSkyN9ArURego9C', '1999-11-11', 'Male', 'Sandton', 'Dog person. Will show you 40 photos of my dog within 10 minutes.', 0, NULL, 0, 0, 0),
(14, 'Qhawe', 'Van Wyk', 'qhawe.vanwyk14@example.com', '$2b$10$Oor2oh/guA7RuKLKPJMuZuRsFCo0LWGfYNWJtFtSkyN9ArURego9C', '2004-11-10', 'Everyone', 'Silverton', 'Reads more than is socially acceptable. Ask me for recommendations.', 0, NULL, 0, 0, 0),
(15, 'Bianca', 'Khumalo', 'bianca.khumalo15@example.com', '$2b$10$Oor2oh/guA7RuKLKPJMuZuRsFCo0LWGfYNWJtFtSkyN9ArURego9C', '1997-08-01', 'Male', 'Menlyn', 'Will talk your ear off about rugby. Also a decent cook.', 0, NULL, 0, 0, 0),
(16, 'Mia', 'Mahlangu', 'mia.mahlangu16@example.com', '$2b$10$Oor2oh/guA7RuKLKPJMuZuRsFCo0LWGfYNWJtFtSkyN9ArURego9C', '2005-09-25', 'Male', 'Johannesburg', 'Trying every coffee shop in the city, one flat white at a time.', 0, NULL, 0, 0, 0),
(17, 'Vuyo', 'Pretorius', 'vuyo.pretorius17@example.com', '$2b$10$Oor2oh/guA7RuKLKPJMuZuRsFCo0LWGfYNWJtFtSkyN9ArURego9C', '1998-07-22', 'Everyone', 'Menlyn', 'Trying every coffee shop in the city, one flat white at a time.', 0, NULL, 0, 0, 0),
(18, 'Gideon', 'Bekker', 'gideon.bekker18@example.com', '$2b$10$Oor2oh/guA7RuKLKPJMuZuRsFCo0LWGfYNWJtFtSkyN9ArURego9C', '2000-10-08', 'Male', 'Centurion', 'Trail runs on Saturdays, terrible at board games, great at making toast.', 0, NULL, 0, 0, 0),
(19, 'Jana', 'Sithole', 'jana.sithole19@example.com', '$2b$10$Oor2oh/guA7RuKLKPJMuZuRsFCo0LWGfYNWJtFtSkyN9ArURego9C', '1999-11-16', 'Male', 'Silverton', 'Spends too much time debating tabs vs spaces. Loves a good braai.', 0, NULL, 0, 0, 0),
(23, 'Caleb', 'Joubert', 'caleb.joubert23@example.com', '$2b$10$Oor2oh/guA7RuKLKPJMuZuRsFCo0LWGfYNWJtFtSkyN9ArURego9C', '2004-11-14', 'Female', 'Hatfield', 'Dog person. Will show you 40 photos of my dog within 10 minutes.', 0, NULL, 0, 0, 0),
(24, 'Willow', 'Naidoo', 'willow.naidoo24@example.com', '$2b$10$Oor2oh/guA7RuKLKPJMuZuRsFCo0LWGfYNWJtFtSkyN9ArURego9C', '2001-04-18', 'Female', 'Johannesburg', 'Dog person. Will show you 40 photos of my dog within 10 minutes.', 0, NULL, 0, 0, 0),
(25, 'Ethan', 'Van Wyk', 'ethan.vanwyk25@example.com', '$2b$10$Oor2oh/guA7RuKLKPJMuZuRsFCo0LWGfYNWJtFtSkyN9ArURego9C', '2004-01-21', 'Everyone', 'Pretoria', 'Coffee snob, plant parent, always up for a Sunday market.', 0, NULL, 0, 0, 0),
(26, 'Tanya', 'Khumalo', 'tanya.khumalo26@example.com', '$2b$10$Oor2oh/guA7RuKLKPJMuZuRsFCo0LWGfYNWJtFtSkyN9ArURego9C', '2006-03-13', 'Male', 'Hatfield', 'Will talk your ear off about rugby. Also a decent cook.', 0, NULL, 0, 0, 0),
(27, 'Hannah', 'Mahlangu', 'hannah.mahlangu27@example.com', '$2b$10$Oor2oh/guA7RuKLKPJMuZuRsFCo0LWGfYNWJtFtSkyN9ArURego9C', '1998-04-02', 'Everyone', 'Silverton', 'Trail runs on Saturdays, terrible at board games, great at making toast.', 0, NULL, 0, 0, 0),
(28, 'Imran', 'Pretorius', 'imran.pretorius28@example.com', '$2b$10$Oor2oh/guA7RuKLKPJMuZuRsFCo0LWGfYNWJtFtSkyN9ArURego9C', '2005-03-03', 'Everyone', 'Centurion', 'Weekend hiker, weekday spreadsheet enthusiast.', 0, NULL, 0, 0, 0),
(29, 'Amahle', 'Bekker', 'amahle.bekker29@example.com', '$2b$10$Oor2oh/guA7RuKLKPJMuZuRsFCo0LWGfYNWJtFtSkyN9ArURego9C', '2005-07-22', 'Everyone', 'Waterkloof', 'Big fan of long drives with no real destination.', 0, NULL, 0, 0, 0),
(30, 'Dineo', 'Sithole', 'dineo.sithole30@example.com', '$2b$10$Oor2oh/guA7RuKLKPJMuZuRsFCo0LWGfYNWJtFtSkyN9ArURego9C', '1998-08-11', 'Male', 'Pretoria', 'Trying every coffee shop in the city, one flat white at a time.', 0, NULL, 0, 0, 0),
(31, 'Uwe', 'Joubert', 'uwe.joubert31@example.com', '$2b$10$Oor2oh/guA7RuKLKPJMuZuRsFCo0LWGfYNWJtFtSkyN9ArURego9C', '2005-04-12', 'Female', 'Johannesburg', 'Trying every coffee shop in the city, one flat white at a time.', 0, NULL, 0, 0, 0);

-- USER PHOTOS
INSERT INTO user_photos (user_id, photo_path, sort_order) VALUES
(5, 'user5_c7bd09b7fc35.jpg', 0),
(6, 'user6_c1724554c49b.jpg', 0),
(7, 'user7_12d502f3f96d.jpg', 0),
(7, 'user7_97796061d36e.jpg', 1),
(8, 'user8_d75ac028e28e.jpg', 0),
(9, 'user9_eb024cb77684.jpg', 0),
(10, 'user10_75d5e8ba8db5.jpg', 0),
(11, 'user11_ed4374192f26.jpg', 0),
(12, 'user12_27a2ed908a24.jpg', 0),
(13, 'user13_252d1bbeb1f8.jpg', 0),
(14, 'user14_da45b59d0d5b.jpg', 0),
(15, 'user15_593980aceaf9.jpg', 0),
(16, 'user16_489fb19c283a.jpg', 0),
(17, 'user17_f4a8c50a73a5.jpg', 0),
(18, 'user18_c242f4e38d49.jpg', 0),
(19, 'user19_912319bb9cc5.jpg', 0),
(23, 'user23_7af921631586.jpg', 0),
(23, 'user23_f263b0a94676.jpg', 1),
(24, 'user24_4ccff8041e95.jpg', 0),
(24, 'user24_90340fa75470.jpg', 1),
(25, 'user25_14a71ec10cd3.jpg', 0),
(26, 'user26_0bdf4c6f0852.jpg', 0),
(27, 'user27_fe444bbe0486.jpg', 0),
(28, 'user28_39f632e0846b.jpg', 0),
(29, 'user29_6787415fce09.jpg', 0),
(29, 'user29_6ea37584d4c8.jpg', 1),
(30, 'user30_585f7edd71bb.jpg', 0),
(30, 'user30_d8cd5d7e2a16.jpg', 1),
(31, 'user31_a7bdc5b74a38.jpg', 0);

-- USER HOBBIES
INSERT INTO user_hobbies (user_id, hobby) VALUES
(5, 'Running'),
(5, 'Gaming'),
(5, 'Rugby'),
(6, 'Photography'),
(6, 'Travel'),
(6, 'Cooking'),
(6, 'Yoga'),
(7, 'Cycling'),
(7, 'Photography'),
(8, 'Cooking'),
(8, 'Reading'),
(8, 'Live music'),
(8, 'Cycling'),
(9, 'Travel'),
(9, 'Painting'),
(9, 'Cooking'),
(10, 'Cycling'),
(10, 'Live music'),
(11, 'Board games'),
(11, 'Photography'),
(12, 'Chess'),
(12, 'Painting'),
(12, 'Live music'),
(13, 'Rock climbing'),
(13, 'Hiking'),
(14, 'Running'),
(14, 'Coffee tasting'),
(15, 'Board games'),
(15, 'Cycling'),
(16, 'Reading'),
(16, 'Painting'),
(16, 'Chess'),
(16, 'Baking'),
(17, 'Rock climbing'),
(17, 'Photography'),
(17, 'Cycling'),
(17, 'Live music'),
(18, 'Cooking'),
(18, 'Board games'),
(19, 'Surfing'),
(19, 'Dancing'),
(19, 'Gardening'),
(19, 'Cycling'),
(23, 'Board games'),
(23, 'Photography'),
(23, 'Dancing'),
(24, 'Painting'),
(24, 'Rock climbing'),
(25, 'Reading'),
(25, 'Baking'),
(26, 'Yoga'),
(26, 'Baking'),
(26, 'Live music'),
(27, 'Rugby'),
(27, 'Board games'),
(27, 'Surfing'),
(27, 'Gardening'),
(28, 'Photography'),
(28, 'Surfing'),
(28, 'Cycling'),
(29, 'Painting'),
(29, 'Gaming'),
(29, 'Rugby'),
(30, 'Surfing'),
(30, 'Photography'),
(30, 'Cooking'),
(30, 'Gaming'),
(31, 'Yoga'),
(31, 'Chess'),
(31, 'Hiking'),
(31, 'Dancing');

-- USER PERSONALITY
INSERT INTO user_personality (user_id, introvert_extrovert, analytical_creative, tech_savvy, sporty) VALUES
(5, 18, 16, 53, 17),
(6, 85, 84, 51, 78),
(7, 53, 40, 63, 86),
(8, 25, 64, 53, 39),
(9, 32, 77, 45, 32),
(10, 73, 38, 79, 59),
(11, 24, 85, 25, 92),
(12, 6, 92, 19, 92),
(13, 38, 69, 27, 69),
(14, 25, 74, 72, 5),
(15, 77, 15, 15, 67),
(16, 32, 74, 93, 30),
(17, 13, 48, 7, 80),
(18, 47, 14, 70, 35),
(19, 65, 57, 29, 17),
(23, 56, 48, 18, 36),
(24, 36, 14, 61, 75),
(25, 67, 66, 32, 56),
(26, 89, 67, 24, 29),
(27, 69, 72, 25, 12),
(28, 79, 81, 10, 84),
(29, 35, 38, 55, 21),
(30, 69, 38, 21, 49),
(31, 89, 18, 22, 38);

-- SWIPES (likes/dislikes members have made on each other)
INSERT INTO swipes (swiper_id, target_id, action, reason_short, reason_long) VALUES
(11, 12, 'dislike', 'No spark, honestly', 'We chatted for a while but there wasn''t really a spark between us. Wishing them well though.'),
(11, 13, 'dislike', 'Different life goals', 'Really nice person, but we want very different things in the next few years.'),
(14, 15, 'dislike', 'Too far away', 'Live on opposite sides of the city and neither of us wanted to commute for this.'),
(16, 17, 'dislike', 'Green flags but no chemistry', 'Ticked every box on paper but talking to them just felt like talking to a colleague.'),
(18, 19, 'dislike', 'Just wasn''t feeling it', 'Can''t put my finger on it, the conversation just never clicked the way I hoped it would.'),
(23, 24, 'dislike', 'Great person, wrong timing', 'Timing wasn''t right for either of us right now, but no hard feelings at all.'),
(25, 26, 'dislike', 'No spark, honestly', 'We chatted for a while but there wasn''t really a spark between us. Wishing them well though.'),
(27, 28, 'dislike', 'Different life goals', 'Really nice person, but we want very different things in the next few years.'),
(29, 30, 'dislike', 'Too far away', 'Live on opposite sides of the city and neither of us wanted to commute for this.'),
(12, 14, 'dislike', 'Green flags but no chemistry', 'Ticked every box on paper but talking to them just felt like talking to a colleague.'),
(13, 16, 'dislike', 'Just wasn''t feeling it', 'Can''t put my finger on it, the conversation just never clicked the way I hoped it would.'),
(17, 18, 'dislike', 'Great person, wrong timing', 'Timing wasn''t right for either of us right now, but no hard feelings at all.'),
(19, 23, 'dislike', 'No spark, honestly', 'We chatted for a while but there wasn''t really a spark between us. Wishing them well though.'),
(24, 25, 'dislike', 'Different life goals', 'Really nice person, but we want very different things in the next few years.'),
(26, 27, 'dislike', 'Too far away', 'Live on opposite sides of the city and neither of us wanted to commute for this.'),
(28, 29, 'dislike', 'Green flags but no chemistry', 'Ticked every box on paper but talking to them just felt like talking to a colleague.'),
(30, 31, 'dislike', 'Just wasn''t feeling it', 'Can''t put my finger on it, the conversation just never clicked the way I hoped it would.'),
(31, 11, 'dislike', 'Great person, wrong timing', 'Timing wasn''t right for either of us right now, but no hard feelings at all.'),
(9, 11, 'like', NULL, NULL),
(10, 12, 'like', NULL, NULL),
(13, 17, 'like', NULL, NULL),
(15, 19, 'like', NULL, NULL),
(23, 27, 'like', NULL, NULL),
(24, 28, 'like', NULL, NULL),
(25, 31, 'like', NULL, NULL);

-- MATCHES
-- 1) users 5 & 6: matched and currently active (used for the chat/match-space demo)
INSERT INTO swipes (swiper_id, target_id, action) VALUES (5, 6, 'like'), (6, 5, 'like');
INSERT INTO matches (id, user1_id, user2_id, status) VALUES (1, 5, 6, 'active');
UPDATE users SET is_matched = 1, current_match_id = 1 WHERE id IN (5, 6);

-- 2) users 7 & 8: matched, then unmatched with a public reason (feeds the leaderboard)
INSERT INTO swipes (swiper_id, target_id, action) VALUES (7, 8, 'like'), (8, 7, 'like');
INSERT INTO matches (id, user1_id, user2_id, status, ended_at, ended_by, end_reason_short, end_reason_long) VALUES
(2, 7, 8, 'ended', NOW() - INTERVAL 3 DAY, 7, 'No spark, honestly', 'We chatted for a while but there wasn''t really a spark between us. Wishing them well though.');

-- 3) users 9 & 10: matched, then unmatched with a different public reason
INSERT INTO swipes (swiper_id, target_id, action) VALUES (9, 10, 'like'), (10, 9, 'like');
INSERT INTO matches (id, user1_id, user2_id, status, ended_at, ended_by, end_reason_short, end_reason_long) VALUES
(3, 9, 10, 'ended', NOW() - INTERVAL 1 DAY, 10, 'Different life goals', 'Really nice person, but we want very different things in the next few years.');

-- MESSAGES for the active match between users 5 & 6
INSERT INTO messages (match_id, sender_id, message) VALUES
(1, 5, 'Hey! I really liked your hiking photos, where was that last one taken?'),
(1, 6, 'Thank you! That one was up at the Cradle of Humankind, been going there for years.'),
(1, 5, 'No way, I love it there. We should go sometime.'),
(1, 6, 'Definitely, I''m free most weekends.');


SET FOREIGN_KEY_CHECKS = 1;
