
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- donateur
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `donateur`;

CREATE TABLE `donateur`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `nom` VARCHAR(255),
    `prenom` VARCHAR(255),
    `email` VARCHAR(255),
    `rue` TEXT,
    `cp` VARCHAR(20),
    `ville` VARCHAR(255),
    `pays` VARCHAR(255),
    `commentaire` TEXT,
    `ident_paiement` VARCHAR(255),
    `total` DOUBLE,
    `date_creation` DATETIME,
    `type_donateur` VARCHAR(255),
    PRIMARY KEY (`id`),
    INDEX `donateur_ident_paiement` (`ident_paiement`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- don
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `don`;

CREATE TABLE `don`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `ident_paiement` VARCHAR(255),
    `montant` DOUBLE,
    `date_paiement` DATETIME,
    `via` VARCHAR(255),
    `moyen_paiement` VARCHAR(255),
    `statut_paiement` VARCHAR(255),
    `frais` DOUBLE,
    PRIMARY KEY (`id`),
    INDEX `don_ident_paiement` (`ident_paiement`),
    INDEX `don_date_paiement` (`date_paiement`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- paypal_info
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `paypal_info`;

CREATE TABLE `paypal_info`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `donateur_id` INTEGER,
    `don_id` INTEGER,
    `item_name` VARCHAR(255),
    `item_number` VARCHAR(255),
    `reference` VARCHAR(255),
    PRIMARY KEY (`id`),
    INDEX `FI__don_paypal_info` (`don_id`),
    INDEX `FI__donateur_paypal_info` (`donateur_id`),
    CONSTRAINT `Rel_don_paypal_info`
        FOREIGN KEY (`don_id`)
        REFERENCES `don` (`id`)
        ON DELETE CASCADE,
    CONSTRAINT `Rel_donateur_paypal_info`
        FOREIGN KEY (`donateur_id`)
        REFERENCES `donateur` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- cmcic_info
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `cmcic_info`;

CREATE TABLE `cmcic_info`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `donateur_id` INTEGER,
    `don_id` INTEGER,
    `cvx` VARCHAR(255),
    `validite_carte` VARCHAR(4),
    `brand` VARCHAR(255),
    `status3ds` VARCHAR(255),
    `motif_refus` VARCHAR(255),
    `recouvrement` TINYINT(1),
    `lib_recouvrement` VARCHAR(255),
    `annulation` TINYINT(1),
    `lib_annulation` VARCHAR(255),
    `date_annulation` DATETIME,
    PRIMARY KEY (`id`),
    INDEX `FI__don_cmcic_info` (`don_id`),
    INDEX `FI__donateur_cmcic_info` (`donateur_id`),
    CONSTRAINT `Rel_don_cmcic_info`
        FOREIGN KEY (`don_id`)
        REFERENCES `don` (`id`)
        ON DELETE CASCADE,
    CONSTRAINT `Rel_donateur_cmcic_info`
        FOREIGN KEY (`donateur_id`)
        REFERENCES `donateur` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- recu_fiscal
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `recu_fiscal`;

CREATE TABLE `recu_fiscal`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `numero` INTEGER,
    `date_creation` DATETIME,
    `ident_paiement` VARCHAR(255),
    `nom` VARCHAR(255),
    `prenom` VARCHAR(255),
    `email` VARCHAR(255),
    `rue` TEXT,
    `cp` VARCHAR(20),
    `ville` VARCHAR(255),
    `pays` VARCHAR(255),
    `montant` DOUBLE,
    `moyen_paiement` VARCHAR(255),
    `date_don_debut` DATETIME,
    `date_don_fin` DATETIME,
    `recurrent` TINYINT(1),
    `filename` VARCHAR(255),
    `envoye` TINYINT(1),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- recu_fiscal_has_don
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `recu_fiscal_has_don`;

CREATE TABLE `recu_fiscal_has_don`
(
    `recu_fiscal_id` INTEGER NOT NULL,
    `don_id` INTEGER NOT NULL,
    PRIMARY KEY (`recu_fiscal_id`,`don_id`),
    INDEX `FI__don_recu_fiscal_has_don` (`don_id`),
    CONSTRAINT `Rel_don_recu_fiscal_has_don`
        FOREIGN KEY (`don_id`)
        REFERENCES `don` (`id`)
        ON DELETE CASCADE,
    CONSTRAINT `Rel_recu_fiscal_recu_fiscal_has_don`
        FOREIGN KEY (`recu_fiscal_id`)
        REFERENCES `recu_fiscal` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
