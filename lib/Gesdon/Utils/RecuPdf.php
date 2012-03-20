<?php
# GesDons
# Application de gestion de dons et de reçus fiscaux
# Copyright (C) 2009 Leblanc Simon <contact@leblanc-simon.eu>
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

namespace Gesdon\Utils;

use Gesdon\Core\Config;
use Gesdon\Database\RecuFiscal;

define('PDF_DOWNLOAD', 'I');
define('PDF_SAVE', 'F');
define('font_DEFAULT', 'Times');

class RecuPdf
{
    private $recu;
    private $pdf;
    
    public function __construct()
    {
        $this->recu = null;
    }
    
    public function init(RecuFiscal $recu)
    {
        $this->recu = $recu;
        $this->pdf  = new \FPDF('P','mm','A5');
        
        $this->pdf->SetMargins(0, 0);
        $this->pdf->SetAutoPageBreak(false);
        
        $this->pdf->AddFont('Vera', '', 'Vera.php');
        $this->pdf->AddFont('Vera', 'B', 'VeraBd.php');
        $this->pdf->AddFont('Vera', 'BI', 'VeraBI.php');
        $this->pdf->AddFont('Vera', 'I', 'VeraIt.php');
        
        $this->pdf->SetAuthor('Framasoft', true);
        $this->pdf->SetCreator('Framasoft', true);
        $this->pdf->SetSubject('Reçu au titre des dons', true);
        $this->pdf->SetTitle('Reçu au titre des dons', true);
    }
    
    public function generatePDF($save = false)
    {
        $this->pdf->AddPage();
        
        $this->printHeader();
        $this->printBeneficiaire();
        $this->printDonateur();
        $this->printSignature();
        $this->printFooter();
        
        if ($save === true) {
            $destination    = PDF_SAVE;
            $filename       = Config::get('pdf_dir').DIRECTORY_SEPARATOR.$this->recu->getFilename();
        } else {
            $destination    = PDF_DOWNLOAD;
            $filename       = 'recu_framasoft_'.$this->recu->getNumero().'.pdf';
        }
        
        $this->pdf->Output($filename, $destination);
    }
    
    ##########################################################
    #               Impression des elements
    ##########################################################
    
    ##########################################################
    #               Entete
    ##########################################################
    
    private function printHeader()
    {
        $this->printLogo();
        $this->printLicense();
        $this->printCerfa();
        $this->printTitle();
        $this->printNumero();
    }
    
    private function printLogo()
    {
        $this->pdf->Image(Config::get('img_logo'), 8, 5, 50, 12);
    }
    
    private function printLicense()
    {
        $this->pdf->Image(Config::get('img_license'), 144, 10, 2, 50);
    }
    
    private function printCerfa()
    {
        $this->fontHeader();
        $this->fontColorGris();
        $this->fontFontGris();
        
        $this->pdf->SetXY(6, 20);
        $this->pdf->Cell(25, 6, 'D\'après CERFA', 0, 0, 'C', true);
        $this->pdf->SetXY(6, 25);
        $this->pdf->Cell(25, 6, '11580*03', 0, 0, 'C', true);
    }
    
    private function printTitle()
    {
        $this->fontTitreBold();
        $this->pdf->SetXY(33, 19);
        $this->pdf->Cell(80, 6, 'Reçu au titre des dons', 0, 0, 'C', false);
        
        $this->fontTitre();
        $this->pdf->SetXY(33, 24);
        $this->pdf->Cell(80, 6, 'à certains organismes d\'intérêt général', 0, 0, 'C', false);
        
        $this->fontTitreLittle();
        $this->pdf->SetXY(33, 27);
        $this->pdf->Cell(80, 6, 'Articles 200, 238 bis et 885-0 V bis A du Code Général des Impôts', 0, 0, 'C', false);
        
    }
    
    private function printNumero()
    {
        $this->fontHeader();
        
        $this->pdf->SetXY(114, 20);
        $this->pdf->Cell(25, 6, 'Numéro d\'ordre', 0, 0, 'C', false);
        $this->pdf->SetXY(114, 25);
        $this->pdf->Cell(25, 6, $this->recu->getNumero(), 0, 0, 'C', false);
    }
    
    ##########################################################
    #               Bénéficiaire
    ##########################################################
    
    private function printBeneficiaire()
    {
        $this->printBeneficiaireTitle();
        $this->printBeneficiaireName();
        $this->printBeneficiaireAddress();
        $this->printBeneficiaireObject();
        $this->printBeneficiaireOIG();
    }
    
    private function printBeneficiaireTitle()
    {
        $this->fontCadreTitre();
        
        $this->pdf->SetXY(6, 40);
        $this->pdf->Cell(134, 6, 'Bénéficiaire des versements', 0, 0, 'C', true);
    }
    
    private function printBeneficiaireName()
    {
        $this->fontGeneral();
        
        $this->pdf->SetXY(6, 47);
        $this->pdf->Cell(25, 6, 'Nom :', 0, 0, 'L', false);
        
        $this->pdf->SetXY(32, 47);
        $this->pdf->Cell(100, 6, 'Framasoft', 0, 0, 'L', false);
    }
    
    private function printBeneficiaireAddress()
    {
        $this->fontGeneral();
        
        $this->pdf->SetXY(6, 51);
        $this->pdf->Cell(25, 6, 'Adresse :', 0, 0, 'L', false);
        
        $this->pdf->SetXY(32, 51);
        $this->pdf->Cell(100, 6, 'c/o T. CEZARD, 5 avenue Stephen PICHON, 75013 PARIS', 0, 0, 'L', false);
    }
    
    private function printBeneficiaireObject()
    {
        $this->fontGeneral();
        
        $this->pdf->SetXY(6, 55);
        $this->pdf->Cell(25, 6, 'Objet :', 0, 0, 'L', false);
        
        $this->fontObject();
        $this->pdf->SetXY(32, 55);
        //MultiCell suck!!!
        //$this->pdf->MultiCell(100, 12, 'L\'association a pour objet la diffusion et la promotion de la culture libre en général et du logiciel libre en particulier.', 0, 'L', false);
        $this->pdf->Cell(100, 6, 'L\'association a pour objet la diffusion et la promotion de la culture libre', 0, 0, 'L', false);
        $this->pdf->SetXY(32, 59);
        $this->pdf->Cell(100, 6, 'en général et du logiciel libre en particulier.', 0, 0, 'L', false);
    }
    
    private function printBeneficiaireOIG()
    {
        $this->pdf->SetXY(7.5, 65.5);
        $this->printCheckBox(true);
        
        $this->fontCheckbox();
        $this->pdf->SetXY(12, 64);
        $this->pdf->Cell(100, 6, 'Oeuvre ou organisme d\'intérêt général', 0, 0, 'L', false);
    }
    
    ##########################################################
    #               Donateur
    ##########################################################
    
    private function printDonateur()
    {
        $this->printDonateurTitle();
        $this->printDonateurName();
        $this->printDonateurAddress();
        $this->printDonateurReconnait();
        $this->printDonateurDon();
        $this->printDonateurDonLettre();
        $this->printDonateurDateDon();
        $this->printDonateurCertifie();
        $this->printDonateurForme();
        $this->printDonateurNature();
        $this->printDonateurMode();
    }
    
    private function printDonateurTitle()
    {
        $this->fontCadreTitre();
        
        $this->pdf->SetXY(6, 75);
        $this->pdf->Cell(134, 6, 'Donateur', 0, 0, 'C', true);
    }
    
    private function printDonateurName()
    {
        $this->fontGeneral();
        
        $this->pdf->SetXY(6, 82);
        $this->pdf->Cell(25, 6, 'Nom :', 0, 0, 'L', false);
        
        $this->pdf->SetXY(32, 82);
        $this->pdf->Cell(100, 6, $this->recu->getNom().' '.$this->recu->getPrenom(), 0, 0, 'L', false);
    }
    
    private function printDonateurAddress()
    {
        $this->fontGeneral();
        
        $y = 86;
        
        $this->pdf->SetXY(6, $y);
        $this->pdf->Cell(25, 6, 'Adresse :', 0, 0, 'L', false);
        
        $address = str_replace("\r\n", "\n", $this->recu->getRue()."\n".$this->recu->getCp().' '.$this->recu->getVille());
        $address = str_replace("\r", "\n", $address);
        
        $address = explode("\n", $address);
        
        foreach($address as $line) {
            $this->pdf->SetXY(32, $y);
            $this->pdf->Cell(100, 6, $line, 0, 0, 'L', false);
            $y += 4;
        }
    }
    
    private function printDonateurReconnait()
    {
        $this->fontGeneral();
        
        $this->pdf->SetXY(6, 98);
        $this->pdf->Cell(134, 6, 'Le bénéficiaire reconnaît avoir reçu au titre des dons et versements ouvrant droit à réduction', 0, 0, 'L', false);
        $this->pdf->SetXY(6, 102);
        $this->pdf->Cell(134, 6, 'd\'impôt, la somme de :', 0, 0, 'L', false);
    }
    
    private function printDonateurDon()
    {
        $this->fontDon();
        
        $this->pdf->SetXY(65, 106);
        $this->pdf->Cell(134, 6, $this->recu->getMontant().' euros', 0, 0, 'L', false);
    }
    
    private function printDonateurDonLettre()
    {
        $this->fontGeneral();
        $this->pdf->SetXY(6, 111);
        $this->pdf->Cell(25, 6, 'Somme en toutes lettres :', 0, 0, 'L', false);
        
        $this->fontDonText();
        $this->pdf->SetXY(43, 111);
        $this->pdf->Cell(90, 6, $this->recu->getMontantTexte(), 0, 0, 'L', false);
    }
    
    private function printDonateurDateDon()
    {
        $this->fontGeneral();
        $this->pdf->SetXY(6, 115);
        if ($this->recu->getRecurrent() === true) {
            $this->pdf->Cell(25, 6, 'Date du don (récurrent) : '.$this->recu->getDateDonTexte(), 0, 0, 'L', false);
        } else {
            $this->pdf->Cell(25, 6, 'Date du versement ou du don :', 0, 0, 'L', false);
            $this->fontDonText();
            $this->pdf->SetXY(50, 115);
            $this->pdf->Cell(90, 6, $this->recu->getDateDonTexte(), 0, 0, 'L', false);
        }
    }
    
    private function printDonateurCertifie()
    {
        $this->fontGeneral();
        
        $this->pdf->SetXY(6, 119);
        $this->pdf->Cell(134, 6, 'Le bénéficiaire certifie sur l\'honneur que les dons et versements qu\'il reçoit ouvrent droits à', 0, 0, 'L', false);
        $this->pdf->SetXY(6, 123);
        $this->pdf->Cell(134, 6, 'la réduction d\'impôt prévue à l\'article :', 0, 0, 'L', false);
        
        $this->pdf->SetXY(7.5, 128.5);
        $this->printCheckBox(true);
        $this->fontCheckbox();
        $this->pdf->SetXY(12, 127);
        $this->pdf->Cell(100, 6, '200 du CGI', 0, 0, 'L', false);
        
        $this->pdf->SetXY(40.5, 128.5);
        $this->printCheckBox(true);
        $this->fontCheckbox();
        $this->pdf->SetXY(45, 127);
        $this->pdf->Cell(100, 6, '238 bis du CGI', 0, 0, 'L', false);
        
        $this->pdf->SetXY(78.5, 128.5);
        $this->printCheckBox(false);        
        $this->fontCheckbox();
        $this->pdf->SetXY(83, 127);
        $this->pdf->Cell(100, 6, '885-0 V bis A du C', 0, 0, 'L', false);
    }
    
    private function printDonateurForme()
    {
        $this->fontDonText();
        
        $this->pdf->SetXY(6, 135);
        $this->pdf->Cell(134, 6, 'Forme du don :', 0, 0, 'L', false);
        
        $this->pdf->SetXY(7.5, 141.5);
        $this->printCheckBox(false);
        $this->fontCheckbox();
        $this->pdf->SetXY(12, 140);
        $this->pdf->Cell(100, 6, 'Acte authentique', 0, 0, 'L', false);
        
        $this->pdf->SetXY(40.5, 141.5);
        $this->printCheckBox(false);
        $this->fontCheckbox();
        $this->pdf->SetXY(45, 140);
        $this->pdf->Cell(100, 6, 'Acte sous seing privé', 0, 0, 'L', false);
        
        $this->pdf->SetXY(78.5, 141.5);
        $this->printCheckBox(true);
        $this->fontCheckbox();
        $this->pdf->SetXY(83, 140);
        $this->pdf->Cell(100, 6, 'Déclaration de don manuel', 0, 0, 'L', false);
        
        $this->pdf->SetXY(123.5, 141.5);
        $this->printCheckBox(false); 
        $this->fontCheckbox();
        $this->pdf->SetXY(128, 140);
        $this->pdf->Cell(100, 6, 'Autre', 0, 0, 'L', false);
    }
    
    private function printDonateurNature()
    {
        $this->fontDonText();
        
        $this->pdf->SetXY(6, 146);
        $this->pdf->Cell(134, 6, 'Nature du don :', 0, 0, 'L', false);
        
        $this->pdf->SetXY(7.5, 152.5);
        $this->printCheckBox(true);
        $this->fontCheckbox();
        $this->pdf->SetXY(12, 151);
        $this->pdf->Cell(100, 6, 'Numéraire', 0, 0, 'L', false);
        
        $this->pdf->SetXY(40.5, 152.5);
        $this->printCheckBox(false);
        $this->fontCheckbox();
        $this->pdf->SetXY(45, 151);
        $this->pdf->Cell(100, 6, 'Titre de sociétés côtés', 0, 0, 'L', false);
        
        $this->pdf->SetXY(78.5, 152.5);
        $this->printCheckBox(false);
        $this->fontCheckbox();
        $this->pdf->SetXY(83, 151);
        $this->pdf->Cell(100, 6, 'Autre', 0, 0, 'L', false);
    }
    
    private function printDonateurMode()
    {
        $this->fontDonText();
        
        $this->pdf->SetXY(6, 157);
        $this->pdf->Cell(134, 6, 'En cas de don en numéraire, mode de versement du don', 0, 0, 'L', false);
        
        $this->pdf->SetXY(7.5, 163.5);
        if ($this->recu->getMoyenPaiement() == 'Espèce') {
            $this->printCheckBox(true);
        } else {
            $this->printCheckBox(false);
        }
        $this->fontCheckbox();
        $this->pdf->SetXY(12, 162);
        $this->pdf->Cell(100, 6, 'Remise d\'espèces', 0, 0, 'L', false);
        
        $this->pdf->SetXY(40.5, 163.5);
        if ($this->recu->getMoyenPaiement() == 'Chèque') {
            $this->printCheckBox(true);
        } else {
            $this->printCheckBox(false);
        }
        $this->fontCheckbox();
        $this->pdf->SetXY(45, 162);
        $this->pdf->Cell(100, 6, 'Chèque', 0, 0, 'L', false);
        
        $this->pdf->SetXY(78.5, 163.5);
        if ($this->recu->getMoyenPaiement() == 'Carte bancaire' || $this->recu->getMoyenPaiement() == 'Virement') {
            $this->printCheckBox(true);
        } else {
            $this->printCheckBox(false);
        }
        $this->fontCheckbox();
        $this->pdf->SetXY(83, 162);
        $this->pdf->Cell(100, 6, 'Virement, prélévement, carte bancaire', 0, 0, 'L', false);
    }
    
    ##########################################################
    #               Footer
    ##########################################################
    
    private function printSignature()
    {
        $this->fontCheckbox();
        $this->pdf->SetXY(88, 170);
        $this->pdf->Rect($this->pdf->GetX(), $this->pdf->GetY(), 50, 21, 'D');
        
        $this->pdf->Cell(20, 6, 'Date et signature : Le '.date('d/m/Y'), 0, 0, 'L', false);
        
        $this->pdf->Image(Config::get('img_signature'), 90, 175, 38, 15);
    }
    
    private function printFooter()
    {
        $this->fontFooter();
        
        $this->pdf->SetXY(6, 192);
        $this->pdf->Cell(134, 6, 'FRAMASOFT - Association loi 1901 déclarée en sous-préfecture d\'Arles le 2 décembre 2003', 0, 0, 'C', false);
        $this->pdf->SetXY(6, 195);
        $this->pdf->Cell(134, 6, 'sous le n° 0132007842 - n° Siret : 500 715 776 00018', 0, 0, 'C', false);
        $this->pdf->SetXY(6, 198);
        $this->pdf->Cell(134, 6, 'Organisme bénéficiant de la franchise des impôts commerciaux au titre de l\'article 261-7-1°-b', 0, 0, 'C', false);
        $this->pdf->SetXY(6, 201);
        $this->pdf->Cell(134, 6, 'du Code Général des Impôts', 0, 0, 'C', false);
    }
    
    ##########################################################
    #               Checkbox
    ##########################################################
    
    private function printCheckBox($checked = false)
    {
        $x = $this->pdf->GetX();
        $y = $this->pdf->GetY();
        
        $this->pdf->Rect($x, $y, 3, 3, 'D');
        if ($checked === true) {
            $this->fontCheckbox();
            $this->pdf->SetXY($x - 1.5, $y - 1.5);
            $this->pdf->Cell(6, 6, 'x', 0, 0, 'C', false);
        }
        
        $this->pdf->SetXY($x, $y);
    }
    
    ##########################################################
    #               Gestion des fonts
    ##########################################################
    
    private function fontTitreBold()
    {
        $this->fontFontBlanc();
        $this->fontColorNoir();
        $this->pdf->SetFont('Vera', 'B', 14);
    }
    
    private function fontTitre()
    {
        $this->fontFontBlanc();
        $this->fontColorNoir();
        $this->pdf->SetFont('Vera', '', 11);
    }
    
    private function fontTitreLittle()
    {
        $this->fontFontBlanc();
        $this->fontColorNoir();
        $this->pdf->SetFont('Vera', '', 5);
    }
    
    private function fontHeader()
    {
        $this->fontFontBlanc();
        $this->fontColorNoir();
        $this->pdf->SetFont('Vera', '', 8);
    }
    
    private function fontCadreTitre()
    {
        $this->fontFontGris();
        $this->fontColorNoir();
        $this->pdf->SetFont('Vera', 'BI', 14);
    }
    
    private function fontGeneral()
    {
        $this->fontFontBlanc();
        $this->fontColorNoir();
        $this->pdf->SetFont(font_DEFAULT, '', 10);
    }
    
    private function fontObject()
    {
        $this->fontFontBlanc();
        $this->fontColorNoir();
        $this->pdf->SetFont(font_DEFAULT, 'I', 10);
    }
    
    private function fontDon()
    {
        $this->fontFontBlanc();
        $this->fontColorNoir();
        $this->pdf->SetFont(font_DEFAULT, 'BU', 13);
    }
    
    private function fontDonText()
    {
        $this->fontFontBlanc();
        $this->fontColorNoir();
        $this->pdf->SetFont(font_DEFAULT, 'U', 10);
    }
    
    private function fontFooter()
    {
        $this->fontFontBlanc();
        $this->fontColorNoir();
        $this->pdf->SetFont('Vera', '', 6);
    }
    
    private function fontCheckbox()
    {
        $this->fontFontBlanc();
        $this->fontColorNoir();
        $this->pdf->SetFont('Vera', '', 8);
    }
    
    private function fontColorGris()
    {
        $this->pdf->SetTextColor(141, 141, 141);
    }
    
    private function fontColorNoir()
    {
        $this->pdf->SetTextColor(0, 0, 0);
    }
    
    private function fontFontGris()
    {
        $this->pdf->SetFillColor(200, 200, 200);
    }
    
    private function fontFontBlanc()
    {
        $this->pdf->SetFillColor(255, 255, 255);
    }
}