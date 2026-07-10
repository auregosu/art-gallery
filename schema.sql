CREATE DATABASE IF NOT EXISTS art_gallery
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE art_gallery;

CREATE TABLE IF NOT EXISTS artist (
    id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name           VARCHAR(150)  NOT NULL,
    portrait_path  VARCHAR(255)  DEFAULT NULL,
    description    TEXT          DEFAULT NULL,
    birthdate      DATE          DEFAULT NULL,
    death          DATE          DEFAULT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS `user` (
    id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    username       VARCHAR(50)   NOT NULL,
    password_hash  VARCHAR(255)  NOT NULL,
    is_admin       TINYINT(1)    NOT NULL DEFAULT 0,
    created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_user_username (username)
);

CREATE TABLE IF NOT EXISTS painting (
    id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    title          VARCHAR(200)  NOT NULL,
    image_path     VARCHAR(255)  NOT NULL,
    description    TEXT          DEFAULT NULL,
    artist_id      INT UNSIGNED  NOT NULL,
    date_painted   DATE          DEFAULT NULL,
    date_added     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_painting_artist (artist_id),
    KEY idx_painting_added  (date_added),
    CONSTRAINT fk_painting_artist
        FOREIGN KEY (artist_id) REFERENCES artist (id)
        ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS comment (
    id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    painting_id    INT UNSIGNED  NOT NULL,
    user_id        INT UNSIGNED  NOT NULL,
    content        TEXT          NOT NULL,
    created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_comment_painting (painting_id),
    KEY idx_comment_user (user_id),
    CONSTRAINT fk_comment_painting
        FOREIGN KEY (painting_id) REFERENCES painting (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_comment_user
        FOREIGN KEY (user_id) REFERENCES `user` (id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS favourite (
    user_id        INT UNSIGNED  NOT NULL,
    painting_id    INT UNSIGNED  NOT NULL,
    added_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, painting_id),
    KEY idx_favourite_painting (painting_id),
    CONSTRAINT fk_favourite_user
        FOREIGN KEY (user_id) REFERENCES `user` (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_favourite_painting
        FOREIGN KEY (painting_id) REFERENCES painting (id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

--  Username: admin   Password: admin123
INSERT INTO `user` (username, password_hash, is_admin) VALUES
    ('admin', '$2y$12$ImkQIJ.cP8lwyM4lcQ0RKOirxhbzfxgTLT0lKeYZSH826zrK47Eui', 1);
