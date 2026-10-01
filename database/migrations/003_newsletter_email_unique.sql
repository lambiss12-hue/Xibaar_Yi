-- À exécuter une seule fois sur une base créée avant la gestion des doublons de la newsletter.
-- 1. Met les emails en minuscules (Moussa@x.sn et moussa@x.sn sont le même inscrit)
-- 2. Supprime les doublons en gardant la première inscription
-- 3. Ajoute un index UNIQUE pour que la base refuse les doublons à l'avenir

UPDATE newsletter SET email = LOWER(TRIM(email));

DELETE n1 FROM newsletter n1
JOIN newsletter n2 ON n1.email = n2.email AND n1.id > n2.id;

ALTER TABLE newsletter ADD UNIQUE INDEX uniq_newsletter_email (email);
