-- À exécuter une seule fois sur une base créée avant l'ajout du formulaire de contact.
-- Table qui stocke les messages envoyés depuis la page Contact.

USE xibaar_yi;

CREATE TABLE IF NOT EXISTS messages_contact (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nom        VARCHAR(150) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    sujet      VARCHAR(30)  NOT NULL,
    message    TEXT NOT NULL,
    date_envoi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
