DROP TABLE IF EXISTS messages CASCADE;
DROP TABLE IF EXISTS contact CASCADE;
DROP TABLE IF EXISTS compte CASCADE;

-- Comptes
CREATE TABLE compte (
    idcompte SERIAL PRIMARY KEY,
    identifiant VARCHAR(15) UNIQUE NOT NULL,
    motdepasse BYTEA NOT NULL,
    iv BYTEA NOT NULL,
    cle BYTEA NOT NULL
);

-- Contacts
CREATE TABLE contact (
    idcontact SERIAL PRIMARY KEY,
    idpossesseur INT NOT NULL,
    iddestinataire INT NOT NULL,
    statut VARCHAR(50) NOT NULL DEFAULT 'en_attente' CHECK (statut IN ('en_attente', 'accepte', 'bloque')),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_possesseur FOREIGN KEY (idpossesseur) REFERENCES compte(idcompte) ON DELETE CASCADE,
    CONSTRAINT fk_destinataire FOREIGN KEY (iddestinataire) REFERENCES compte(idcompte) ON DELETE CASCADE,
    CONSTRAINT uc_contact UNIQUE (idpossesseur, iddestinataire)
);

-- Messages
CREATE TABLE messages (
    idmessage SERIAL PRIMARY KEY,
    idemetteur INT NOT NULL,
    idreceveur INT NOT NULL,
    contenu TEXT NOT NULL,
    chemin VARCHAR(255) DEFAULT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_emetteur FOREIGN KEY (idemetteur) REFERENCES compte(idcompte) ON DELETE CASCADE,
    CONSTRAINT fk_receveur FOREIGN KEY (idreceveur) REFERENCES compte(idcompte) ON DELETE CASCADE
);
