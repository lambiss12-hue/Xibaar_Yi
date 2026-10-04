-- À exécuter une seule fois sur une base créée avant la limite de tentatives de connexion.
-- Chaque échec de connexion est noté (adresse IP + date) : après 5 échecs en 15 minutes,
-- la même adresse IP doit attendre avant de réessayer (protège contre les robots
-- qui essaient des milliers de mots de passe).

CREATE TABLE IF NOT EXISTS tentatives_connexion (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    ip             VARCHAR(45) NOT NULL,
    date_tentative DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tentatives_ip_date (ip, date_tentative)
);
