<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1388072268.
 * Generated on 2013-12-26 16:37:48 by leviathan
 */
class PropelMigration_1388072268
{

    public function preUp($manager)
    {
        // add the pre-migration code here
    }

    public function postUp($manager)
    {
        // add the post-migration code here
    }

    public function preDown($manager)
    {
        // add the pre-migration code here
    }

    public function postDown($manager)
    {
        // add the post-migration code here
    }

    /**
     * Get the SQL statements for the Up migration
     *
     * @return array list of the SQL strings to execute for the Up migration
     *               the keys being the datasources
     */
    public function getUpSQL()
    {
        return array (
  'gesdon' => '
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE `task_manager`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `task_name` VARCHAR(255) NOT NULL,
    `param` TEXT NOT NULL,
    `date_to_execute` DATETIME NOT NULL,
    `executed` TINYINT(1) DEFAULT 0,
    `executed_at` DATETIME,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

    /**
     * Get the SQL statements for the Down migration
     *
     * @return array list of the SQL strings to execute for the Down migration
     *               the keys being the datasources
     */
    public function getDownSQL()
    {
        return array (
  'gesdon' => '
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `task_manager`;

CREATE TABLE `mail`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255),
    `subject` VARCHAR(255),
    `content` TEXT,
    `recu` TINYINT DEFAULT 1 NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM;

CREATE TABLE `recu_fiscal_old`
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

CREATE TABLE `zz_cmcic`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `tpe` VARCHAR(10) NOT NULL,
    `date` DATETIME NOT NULL,
    `date_retour` DATETIME,
    `montant` FLOAT NOT NULL,
    `reference` VARCHAR(20) NOT NULL,
    `texte_libre` TEXT,
    `mail` VARCHAR(255),
    `nom` VARCHAR(255),
    `prenom` VARCHAR(255),
    `adresse1` VARCHAR(255),
    `adresse2` VARCHAR(255),
    `cp` VARCHAR(20),
    `ville` VARCHAR(255),
    `pays` VARCHAR(255),
    `numauto` VARCHAR(255),
    `code_retour` VARCHAR(255),
    `cvx` VARCHAR(255),
    `vld` VARCHAR(255),
    `brand` VARCHAR(255),
    `status3ds` INTEGER,
    `motif_refus` VARCHAR(255),
    `recurrent` INTEGER DEFAULT 0 NOT NULL,
    `recouvrement` INTEGER DEFAULT 0 NOT NULL,
    `lib_recouvrement` VARCHAR(255),
    `date_fin_prevue` DATETIME,
    `date_recouvrement` DATETIME,
    `annulation` INTEGER DEFAULT 0 NOT NULL,
    `lib_annulation` VARCHAR(255),
    `date_annulation` DATETIME,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `reference` (`reference`(20))
) ENGINE=MyISAM;

CREATE TABLE `zz_paypal_cart_info`
(
    `txnid` VARCHAR(30) DEFAULT \'\' NOT NULL,
    `itemname` VARCHAR(255) DEFAULT \'\' NOT NULL,
    `itemnumber` VARCHAR(50),
    `os0` VARCHAR(20),
    `on0` VARCHAR(50),
    `os1` VARCHAR(20),
    `on1` VARCHAR(50),
    `quantity` CHAR(3) DEFAULT \'\' NOT NULL,
    `invoice` VARCHAR(255) DEFAULT \'\' NOT NULL,
    `custom` VARCHAR(255) DEFAULT \'\' NOT NULL
) ENGINE=MyISAM;

CREATE TABLE `zz_paypal_payment_info`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `firstname` VARCHAR(100),
    `lastname` VARCHAR(100),
    `buyer_email` VARCHAR(100),
    `street` VARCHAR(200),
    `city` VARCHAR(100),
    `state` CHAR(50),
    `zipcode` VARCHAR(11),
    `memo` VARCHAR(255),
    `itemname` VARCHAR(255),
    `itemnumber` VARCHAR(50),
    `os0` VARCHAR(20),
    `on0` VARCHAR(50),
    `os1` VARCHAR(20),
    `on1` VARCHAR(50),
    `quantity` CHAR(3),
    `paymentdate` VARCHAR(50),
    `paymenttype` VARCHAR(10),
    `txnid` VARCHAR(30),
    `mc_gross` DECIMAL,
    `mc_fee` DECIMAL,
    `paymentstatus` VARCHAR(15),
    `pendingreason` VARCHAR(10),
    `txntype` VARCHAR(10),
    `tax` DECIMAL,
    `mc_currency` VARCHAR(5),
    `reasoncode` VARCHAR(20),
    `custom` VARCHAR(255),
    `country` VARCHAR(20),
    `datecreation` DATETIME DEFAULT \'0000-00-00 00:00:00\',
    `remercie` TINYINT DEFAULT 0 NOT NULL,
    `recu` TINYINT DEFAULT 0 NOT NULL,
    `moyen_payment` VARCHAR(20) DEFAULT \'Carte bancaire\' NOT NULL,
    `type_personne` VARCHAR(20) DEFAULT \'undefined\' NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `txnid` (`txnid`(30))
) ENGINE=MyISAM;

CREATE TABLE `zz_paypal_payment_info_tmp`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `firstname` VARCHAR(100),
    `lastname` VARCHAR(100),
    `buyer_email` VARCHAR(100),
    `street` VARCHAR(200),
    `city` VARCHAR(100),
    `state` CHAR(50),
    `zipcode` VARCHAR(11),
    `memo` VARCHAR(255),
    `itemname` VARCHAR(255),
    `itemnumber` VARCHAR(50),
    `os0` VARCHAR(20),
    `on0` VARCHAR(50),
    `os1` VARCHAR(20),
    `on1` VARCHAR(50),
    `quantity` CHAR(3),
    `paymentdate` VARCHAR(50),
    `paymenttype` VARCHAR(10),
    `txnid` VARCHAR(30),
    `mc_gross` DECIMAL,
    `mc_fee` DECIMAL,
    `paymentstatus` VARCHAR(15),
    `pendingreason` VARCHAR(10),
    `txntype` VARCHAR(10),
    `tax` DECIMAL,
    `mc_currency` VARCHAR(5),
    `reasoncode` VARCHAR(20),
    `custom` VARCHAR(255),
    `country` VARCHAR(20),
    `datecreation` DATETIME DEFAULT \'0000-00-00 00:00:00\',
    `remercie` TINYINT DEFAULT 0 NOT NULL,
    `recu` TINYINT DEFAULT 0 NOT NULL,
    `moyen_payment` VARCHAR(20) DEFAULT \'Carte bancaire\' NOT NULL,
    `type_personne` VARCHAR(20) DEFAULT \'undefined\' NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `txnid` (`txnid`(30))
) ENGINE=MyISAM;

CREATE TABLE `zz_paypal_subscription_info`
(
    `subscr_id` VARCHAR(255) DEFAULT \'\' NOT NULL,
    `sub_event` VARCHAR(50) DEFAULT \'\' NOT NULL,
    `subscr_date` VARCHAR(255) DEFAULT \'\' NOT NULL,
    `subscr_effective` VARCHAR(255) DEFAULT \'\' NOT NULL,
    `period1` VARCHAR(255) DEFAULT \'\' NOT NULL,
    `period2` VARCHAR(255) DEFAULT \'\' NOT NULL,
    `period3` VARCHAR(255) DEFAULT \'\' NOT NULL,
    `amount1` VARCHAR(255) DEFAULT \'\' NOT NULL,
    `amount2` VARCHAR(255) DEFAULT \'\' NOT NULL,
    `amount3` VARCHAR(255) DEFAULT \'\' NOT NULL,
    `mc_amount1` VARCHAR(255) DEFAULT \'\' NOT NULL,
    `mc_amount2` VARCHAR(255) DEFAULT \'\' NOT NULL,
    `mc_amount3` VARCHAR(255) DEFAULT \'\' NOT NULL,
    `recurring` VARCHAR(255) DEFAULT \'\' NOT NULL,
    `reattempt` VARCHAR(255) DEFAULT \'\' NOT NULL,
    `retry_at` VARCHAR(255) DEFAULT \'\' NOT NULL,
    `recur_times` VARCHAR(255) DEFAULT \'\' NOT NULL,
    `username` VARCHAR(255) DEFAULT \'\' NOT NULL,
    `password` VARCHAR(255),
    `payment_txn_id` VARCHAR(50) DEFAULT \'\' NOT NULL,
    `subscriber_emailaddress` VARCHAR(255) DEFAULT \'\' NOT NULL,
    `datecreation` DATETIME DEFAULT \'0000-00-00 00:00:00\' NOT NULL,
    `city` VARCHAR(255) NOT NULL,
    `country` VARCHAR(255) NOT NULL,
    `street` VARCHAR(255) NOT NULL,
    `zipcode` VARCHAR(255) NOT NULL
) ENGINE=MyISAM;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}