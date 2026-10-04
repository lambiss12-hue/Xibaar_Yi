-- À exécuter une seule fois sur une base créée avant la gestion des messages lus / non lus.

ALTER TABLE messages_contact ADD COLUMN lu TINYINT(1) NOT NULL DEFAULT 0;
