delimiter $$
DROP TRIGGER IF EXISTS calcul_montant_total$$
CREATE TRIGGER calcul_montant_total AFTER INSERT ON don
FOR EACH ROW BEGIN
  UPDATE donateur SET donateur.total = (SELECT SUM(don.montant) as total FROM don WHERE don.ident_paiement = NEW.ident_paiement AND statut_paiement = 'ok') WHERE donateur.ident_paiement = NEW.ident_paiement;
END$$

delimiter ;