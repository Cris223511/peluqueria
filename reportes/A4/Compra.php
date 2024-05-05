<?php
require('fpdf181/fpdf.php');
define('EURO', chr(128));
define('EURO_VAL', 6.55957);


//////////////////////////////////////
// Public functions                 //
//////////////////////////////////////
//  function sizeOfText( $texte, $larg )
//  function addSociete( $nom, $adresse )
//  function fact_dev( $libelle, $num )
//  function addDevis( $numdev )
//  function addFacture( $numfact )
//  function addDate( $date )
//  function addClient( $ref )
//  function addPageNumber( $page )
//  function addClientAdresse( $adresse )
//  function addReglement( $mode )
//  function addEcheance( $date )
//  function addNumTVA($tva)
//  function addReference($ref)
//  function addCols( $tab )
//  function addLineFormat( $tab )
//  function lineVert( $tab )
//  function addLine( $ligne, $tab )
//  function addRemarque($remarque)
//  function addCadreTVAs()
//  function addCadreEurosFrancs()
//  function addTVAs( $params, $tab_tva, $invoice )
//  function temporaire( $texte )

class PDF_Invoice extends FPDF
{
	// private variables
	var $colonnes;
	var $format;
	var $angle = 0;

	// private functions
	function RoundedRect($x, $y, $w, $h, $r, $style = '')
	{
		$k = $this->k;
		$hp = $this->h;
		if ($style == 'F')
			$op = 'f';
		elseif ($style == 'FD' || $style == 'DF')
			$op = 'B';
		else
			$op = 'S';
		$MyArc = 4 / 3 * (sqrt(2) - 1);
		$this->_out(sprintf('%.2F %.2F m', ($x + $r) * $k, ($hp - $y) * $k));
		$xc = $x + $w - $r;
		$yc = $y + $r;
		$this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - $y) * $k));

		$this->_Arc($xc + $r * $MyArc, $yc - $r, $xc + $r, $yc - $r * $MyArc, $xc + $r, $yc);
		$xc = $x + $w - $r;
		$yc = $y + $h - $r;
		$this->_out(sprintf('%.2F %.2F l', ($x + $w) * $k, ($hp - $yc) * $k));
		$this->_Arc($xc + $r, $yc + $r * $MyArc, $xc + $r * $MyArc, $yc + $r, $xc, $yc + $r);
		$xc = $x + $r;
		$yc = $y + $h - $r;
		$this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - ($y + $h)) * $k));
		$this->_Arc($xc - $r * $MyArc, $yc + $r, $xc - $r, $yc + $r * $MyArc, $xc - $r, $yc);
		$xc = $x + $r;
		$yc = $y + $r;
		$this->_out(sprintf('%.2F %.2F l', ($x) * $k, ($hp - $yc) * $k));
		$this->_Arc($xc - $r, $yc - $r * $MyArc, $xc - $r * $MyArc, $yc - $r, $xc, $yc - $r);
		$this->_out($op);
	}

	function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
	{
		$h = $this->h;
		$this->_out(sprintf(
			'%.2F %.2F %.2F %.2F %.2F %.2F c ',
			$x1 * $this->k,
			($h - $y1) * $this->k,
			$x2 * $this->k,
			($h - $y2) * $this->k,
			$x3 * $this->k,
			($h - $y3) * $this->k
		));
	}

	function Circle($x, $y, $r, $style = 'D')
	{
		$this->Ellipse($x, $y, $r, $r, $style);
	}

	function Ellipse($x, $y, $rx, $ry, $style = 'D')
	{
		if ($style == 'F')
			$op = 'f';
		elseif ($style == 'FD' || $style == 'DF')
			$op = 'B';
		else
			$op = 'S';
		$lx = 4 / 3 * (M_SQRT2 - 1) * $rx;
		$ly = 4 / 3 * (M_SQRT2 - 1) * $ry;
		$k = $this->k;
		$h = $this->h;
		$this->_out(sprintf(
			'%.2F %.2F m %.2F %.2F %.2F %.2F %.2F %.2F c',
			($x + $rx) * $k,
			($h - $y) * $k,
			($x + $rx) * $k,
			($h - ($y - $ly)) * $k,
			($x + $lx) * $k,
			($h - ($y - $ry)) * $k,
			$x * $k,
			($h - ($y - $ry)) * $k
		));
		$this->_out(sprintf(
			'%.2F %.2F %.2F %.2F %.2F %.2F c',
			($x - $lx) * $k,
			($h - ($y - $ry)) * $k,
			($x - $rx) * $k,
			($h - ($y - $ly)) * $k,
			($x - $rx) * $k,
			($h - $y) * $k
		));
		$this->_out(sprintf(
			'%.2F %.2F %.2F %.2F %.2F %.2F c',
			($x - $rx) * $k,
			($h - ($y + $ly)) * $k,
			($x - $lx) * $k,
			($h - ($y + $ry)) * $k,
			$x * $k,
			($h - ($y + $ry)) * $k
		));
		$this->_out(sprintf(
			'%.2F %.2F %.2F %.2F %.2F %.2F c %s',
			($x + $lx) * $k,
			($h - ($y + $ry)) * $k,
			($x + $rx) * $k,
			($h - ($y + $ly)) * $k,
			($x + $rx) * $k,
			($h - $y) * $k,
			$op
		));
	}

	function Rotate($angle, $x = -1, $y = -1)
	{
		if ($x == -1)
			$x = $this->x;
		if ($y == -1)
			$y = $this->y;
		if ($this->angle != 0)
			$this->_out('Q');
		$this->angle = $angle;
		if ($angle != 0) {
			$angle *= M_PI / 180;
			$c = cos($angle);
			$s = sin($angle);
			$cx = $x * $this->k;
			$cy = ($this->h - $y) * $this->k;
			$this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
		}
	}

	function _endpage()
	{
		if ($this->angle != 0) {
			$this->angle = 0;
			$this->_out('Q');
		}
		parent::_endpage();
	}

	// public functions
	function sizeOfText($texte, $largeur)
	{
		$index = 0;
		$nb_lines = 0;
		$loop = true;
		while ($loop) {
			$pos = strpos($texte, "\n");
			if (!$pos) {
				$loop = false;
				$ligne = $texte;
			} else {
				$ligne = substr($texte, $index, $pos);
				$texte = substr($texte, $pos + 1);
			}
			$length = floor($this->GetStringWidth($ligne));
			$res = 0;
			if ($largeur != 0) {
				$res = 1 + floor($length / $largeur);
			}
			$nb_lines += $res;
		}
		return $nb_lines;
	}

	// Company
	function addSociete($nom, $adresse, $logo, $ext_logo)
	{
		$x1 = 33;
		$y1 = 8;
		//Positionnement en bas
		$this->Image($logo, 5, 3, 25, 25, $ext_logo);
		$this->SetXY($x1, $y1);
		$this->SetFont('Arial', 'B', 12);
		$length = $this->GetStringWidth($nom);
		$this->Cell($length, 2, $nom);
		$this->SetXY($x1, $y1 + 4);
		$this->SetFont('Arial', '', 10);
		$length = $this->GetStringWidth($adresse);
		//Coordonnées de la société
		$lignes = $this->sizeOfText($adresse, $length);
		$this->MultiCell($length, 4, $adresse);
	}

	// Label and number of invoice/estimate
	function fact_dev($libelle)
	{
		$r1  = $this->w - 80;
		$r2  = $r1 + 68;
		$y1  = 6;
		$y2  = $y1 + 2;
		$mid = ($r1 + $r2) / 2;

		$texte  = $libelle;
		$szfont = 15;
		$loop   = 0;

		while ($loop == 0) {
			$this->SetFont("Arial", "B", $szfont);
			$sz = $this->GetStringWidth($texte);
			if (($r1 + $sz) > $r2)
				$szfont--;
			else
				$loop++;
		}

		$this->SetLineWidth(0.1);
		$this->SetFillColor(176, 100, 100);
		$this->SetTextColor(255, 255, 255);
		$this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 2.5, 'DF');
		$this->SetXY($r1 + 1, $y1 + 2);
		$this->Cell($r2 - $r1 - 1, 5, $texte, 0, 0, "C");
	}

	// Estimate
	function addDevis($numdev)
	{
		$string = sprintf("DEV%04d", $numdev);
		$this->fact_dev("Devis", $string);
	}

	// Invoice
	function addFacture($numfact)
	{
		$string = sprintf("FA%04d", $numfact);
		$this->fact_dev("Facture", $string);
	}

	function addDate($date)
	{
		$r1  = $this->w - 61;
		$r2  = $r1 + 49;
		$y1  = 17;
		$y2  = $y1;
		$mid = $y1 + ($y2 / 2);
		$this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
		$this->Line($r1, $mid, $r2, $mid);
		$this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 2);
		$this->SetFont("Arial", "B", 10);
		$this->Cell(10, 5, "Fecha y hora", 0, 0, "C");
		$this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 10.5);
		$this->SetFont("Arial", "", 10);
		$this->Cell(10, 5, $date, 0, 0, "C");
	}

	function addMetodoPago($metodo_pago)
	{
		$r1  = $this->w - 61;
		$r2  = $r1 + 49;
		$y1  = 17;
		$y2  = $y1;
		$mid = $y1 + ($y2 / 2);
		$this->RoundedRect($r1, $y1 + 20, ($r2 - $r1), $y2, 3.5, 'D');
		$this->Line($r1, $mid + 20, $r2, $mid + 20);
		$this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 22);
		$this->SetFont("Arial", "B", 10);
		$this->Cell(10, 5, utf8_decode("Método de pago"), 0, 0, "C");
		$this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 30.5);
		$this->SetFont("Arial", "", 10);
		$this->Cell(10, 5, $metodo_pago, 0, 0, "C");
	}

	function addClient($ref)
	{
		$r1  = $this->w - 31;
		$r2  = $r1 + 19;
		$y1  = 17;
		$y2  = $y1;
		$mid = $y1 + ($y2 / 2);
		$this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
		$this->Line($r1, $mid, $r2, $mid);
		$this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 3);
		$this->SetFont("Arial", "B", 10);
		$this->Cell(10, 5, "CLIENT", 0, 0, "C");
		$this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 9);
		$this->SetFont("Arial", "", 10);
		$this->Cell(10, 5, $ref, 0, 0, "C");
	}

	function addPageNumber($page)
	{
		$r1  = $this->w - 80;
		$r2  = $r1 + 19;
		$y1  = 17;
		$y2  = $y1;
		$mid = $y1 + ($y2 / 2);
		$this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
		$this->Line($r1, $mid, $r2, $mid);
		$this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 3);
		$this->SetFont("Arial", "B", 10);
		$this->Cell(10, 5, "PAGE", 0, 0, "C");
		$this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 9);
		$this->SetFont("Arial", "", 10);
		$this->Cell(10, 5, $page, 0, 0, "C");
	}

	// Client address
	function addClientAdresse($cliente, $domicilio, $num_documento, $email, $telefono)
	{
		$r1     = $this->w - 200;
		$r2     = $r1 + 68;
		$y1     = 40;
		$this->SetXY($r1, $y1);
		$this->SetTextColor(0, 0, 0);
		$this->SetFont("Arial", "B", 10);
		$this->MultiCell(60, 4, "CLIENTE");
		$this->SetXY($r1, $y1 + 5);
		$this->SetFont("Arial", "", 10);
		$this->MultiCell(150, 4, $cliente);
		$this->SetXY($r1, $y1 + 10);
		$this->MultiCell(150, 4, $domicilio);
		$this->SetXY($r1, $y1 + 15);
		$this->MultiCell(150, 4, $num_documento);
		$this->SetXY($r1, $y1 + 20);
		$this->MultiCell(150, 4, $email);
		$this->SetXY($r1, $y1 + 25);
		$this->MultiCell(150, 4, $telefono);
	}

	// Mode of payment
	function addReglement($mode)
	{
		$r1  = 10;
		$r2  = $r1 + 60;
		$y1  = 80;
		$y2  = $y1 + 10;
		$mid = $y1 + (($y2 - $y1) / 2);
		$this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2 - $y1), 2.5, 'D');
		$this->Line($r1, $mid, $r2, $mid);
		$this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 1);
		$this->SetFont("Arial", "B", 10);
		$this->Cell(10, 4, "CLIENTE", 0, 0, "C");
		$this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 5);
		$this->SetFont("Arial", "", 10);
		$this->Cell(10, 5, $mode, 0, 0, "C");
	}

	// Expiry date
	function addEcheance($documento, $numero)
	{
		$r1  = 80;
		$r2  = $r1 + 40;
		$y1  = 80;
		$y2  = $y1 + 10;
		$mid = $y1 + (($y2 - $y1) / 2);
		$this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2 - $y1), 2.5, 'D');
		$this->Line($r1, $mid, $r2, $mid);
		$this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 1);
		$this->SetFont("Arial", "B", 10);
		$this->Cell(10, 4, $numero, 0, 0, "C");
		$this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 5);
		$this->SetFont("Arial", "", 10);
		$this->Cell(10, 5, $numero, 0, 0, "C");
	}

	// VAT number
	function addNumTVA($tva)
	{
		$this->SetFont("Arial", "B", 10);
		$r1  = $this->w - 80;
		$r2  = $r1 + 70;
		$y1  = 80;
		$y2  = $y1 + 10;
		$mid = $y1 + (($y2 - $y1) / 2);
		$this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2 - $y1), 2.5, 'D');
		$this->Line($r1, $mid, $r2, $mid);
		$this->SetXY($r1 + 16, $y1 + 1);
		$this->Cell(40, 4, "DIRECCI�N", '', '', "C");
		$this->SetFont("Arial", "", 10);
		$this->SetXY($r1 + 16, $y1 + 5);
		$this->Cell(40, 5, $tva, '', '', "C");
	}

	function addReference($ref)
	{
		$this->SetFont("Arial", "", 10);
		$length = $this->GetStringWidth("R�f�rences : " . $ref);
		$r1  = 10;
		$r2  = $r1 + $length;
		$y1  = 92;
		$y2  = $y1 + 5;
		$this->SetXY($r1, $y1);
		$this->Cell($length, 4, "R�f�rences : " . $ref);
	}

	function addCols($tab, $aligns, $y)
	{
		global $colonnes;

		$r1  = 15;
		$r2  = $this->w - ($r1 * 2) + 2;
		$y1  = $y;
		$y2  = $this->h - 50 - 115;
		$this->SetXY($r1, $y1);
		$this->Rect($r1, $y1, $r2, $y2, "D");
		$this->SetDrawColor(241, 210, 76);
		$this->Line($r1, $y1 + 6, $r1 + $r2, $y1 + 6);

		$colX = $r1;
		$colonnes = $tab;
		foreach ($tab as $lib => $pos) {
			$this->SetXY($colX, $y1 + 2.5);
			$this->Cell($pos, 1, $lib, 0, 0, "C");
			$colX += $pos;
		}
	}

	function addCols2($tab, $aligns, $y)
	{
		global $colonnes;

		$r1  = 15;
		$r2  = $this->w - ($r1 * 2) + 2;
		$y1  = $y;
		$y2  = $this->h - 297;
		$this->SetXY($r1, $y1);
		$this->Rect($r1, $y1, $r2, $y2, "D");
		$this->SetDrawColor(241, 210, 76);
		$this->Line($r1 + 90.5, $y1 + 6, $r1 + $r2, $y1 + 6);

		// $this->SetDrawColor(255, 255, 255);

		$colX = $r1;
		$colonnes = $tab;
		foreach ($tab as $lib => $pos) {
			$this->SetXY($colX + 50.5, $y1 + 2.5);
			$this->Cell($pos, 1, $lib, 0, 0, "C");
			$colX += $pos - 33.5;
		}
	}

	function addLineFormat($tab)
	{
		global $format, $colonnes;

		foreach ($colonnes as $lib => $pos) {
			if (isset($tab["$lib"]))
				$format[$lib] = $tab["$lib"];
		}
	}

	function lineVert($tab)
	{
		global $colonnes;

		reset($colonnes);
		$maxSize = 0;
		foreach ($colonnes as $lib => $pos) {
			$texte = $tab[$lib];
			$longCell  = $pos - 2;
			$size = $this->sizeOfText($texte, $longCell);
			if ($size > $maxSize)
				$maxSize = $size;
		}

		return $maxSize;
	}

	// add a line to the invoice/estimate
	/*    $ligne = array( "REFERENCE"    => $prod["ref"],
                      "DESIGNATION"  => $libelle,
                      "QUANTITE"     => sprintf( "%.2F", $prod["qte"]) ,
                      "P.U. HT"      => sprintf( "%.2F", $prod["px_unit"]),
                      "MONTANT H.T." => sprintf ( "%.2F", $prod["qte"] * $prod["px_unit"]) ,
                      "TVA"          => $prod["tva"] );
*/
	function addLine($ligne, $tab)
	{
		global $colonnes, $format;

		$ordonnee     = 16;
		$maxSize      = $ligne;

		reset($colonnes);
		foreach ($colonnes as $lib => $pos) {
			$longCell  = $pos - 2;
			$texte     = $tab[$lib];
			$length    = $this->GetStringWidth($texte);
			$tailleTexte = $this->sizeOfText($texte, $length);
			$formText  = $format[$lib];
			$this->SetXY($ordonnee, $ligne - 1);
			$this->MultiCell($longCell, 4, $texte, 0, $formText);
			if ($maxSize < ($this->GetY()))
				$maxSize = $this->GetY();
			$ordonnee += $pos;
		}

		return ($maxSize - $ligne);
	}

	function addRemarque($remarque)
	{
		$this->SetFont("Arial", "", 10);
		$length = $this->GetStringWidth("Remarque : " . $remarque);
		$r1  = 10;
		$r2  = $r1 + $length;
		$y1  = $this->h - 45.5;
		$y2  = $y1 + 5;
		$this->SetXY($r1, $y1);
		$this->Cell($length, 4, "Remarque : " . $remarque);
	}

	function addCadreTVAs($monto)
	{
		$this->SetFont("Arial", "B", 8);
		$r1  = 10;
		$r2  = $r1 + 120;
		$y1  = $this->h - 40;
		$y2  = $y1 + 20;
		$this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2 - $y1), 2.5, 'D');
		//$this->Line( $r1, $y1+4, $r2, $y1+4);
		//$this->Line( $r1+5,  $y1+4, $r1+5, $y2); // avant BASES HT
		//$this->Line( $r1+27, $y1, $r1+27, $y2);  // avant REMISE
		//$this->Line( $r1+43, $y1, $r1+43, $y2);  // avant MT TVA
		//$this->Line( $r1+63, $y1, $r1+63, $y2);  // avant % TVA
		//$this->Line( $r1+75, $y1, $r1+75, $y2);  // avant PORT
		//$this->Line( $r1+91, $y1, $r1+91, $y2);  // avant TOTAUX
		$this->SetXY($r1 + 9, $y1 + 3);
		$this->Cell(10, 4, "IMPORTE TOTAL CON LETRA");
		$this->SetFont("Arial", "", 8);
		$this->SetXY($r1 + 9, $y1 + 7);
		$this->MultiCell(100, 4, $monto);
		//$this->SetX( $r1+29 );
		//$this->Cell(10,4, "REMISE");
		//$this->SetX( $r1+48 );
		//$this->Cell(10,4, "MT TVA");
		//$this->SetX( $r1+63 );
		//$this->Cell(10,4, "% TVA");
		//$this->SetX( $r1+78 );
		//$this->Cell(10,4, "PORT");
		//$this->SetX( $r1+100 );
		//$this->Cell(10,4, "TOTAUX");
		//$this->SetFont( "Arial", "B", 6);
		//$this->SetXY( $r1+93, $y2 - 8 );
		//$this->Cell(6,0, "H.T.   :");
		//$this->SetXY( $r1+93, $y2 - 3 );
		//$this->Cell(6,0, "T.V.A. :");
	}

	function addCadreEurosFrancs($impuesto)
	{
		$r1  = $this->w - 75;
		$r2  = $r1 + 65;
		$y1  = $this->h - 40;
		$y2  = $y1 + 20;
		$this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2 - $y1), 2.5, 'D');
		$this->Line($r1 + 28,  $y1, $r1 + 28, $y2); // avant EUROS
		//$this->Line( $r1+20, $y1+4, $r2, $y1+4); // Sous Euros & Francs
		//$this->Line( $r1+38,  $y1, $r1+38, $y2); // Entre Euros & Francs
		$this->SetFont("Arial", "B", 8);
		$this->SetXY($r1 + 34, $y1 + 1);
		$this->Cell(15, 4, "TOTALES", 0, 0, "C");
		$this->SetFont("Arial", "", 8);
		//$this->SetXY( $r1+42, $y1 );
		//$this->Cell(15,4, "FRANCS", 0, 0, "C");
		$this->SetFont("Arial", "B", 6);
		$this->SetXY($r1 + 4, $y1 + 5);
		$this->Cell(20, 4, "OPERACION GRAVADA", 0, 0, "C");
		$this->SetXY($r1 + 4, $y1 + 10);
		$this->Cell(20, 4, $impuesto, 0, 0, "C");
		$this->SetXY($r1 + 4, $y1 + 15);
		$this->Cell(20, 4, "TOTAL A PAGAR", 0, 0, "C");
	}

	// remplit les cadres TVA / Totaux et la remarque
	// params  = array( "RemiseGlobale" => [0|1],
	//                      "remise_tva"     => [1|2...],  // {la remise s'applique sur ce code TVA}
	//                      "remise"         => value,     // {montant de la remise}
	//                      "remise_percent" => percent,   // {pourcentage de remise sur ce montant de TVA}
	//                  "FraisPort"     => [0|1],
	//                      "portTTC"        => value,     // montant des frais de ports TTC
	//                                                     // par defaut la TVA = 19.6 %
	//                      "portHT"         => value,     // montant des frais de ports HT
	//                      "portTVA"        => tva_value, // valeur de la TVA a appliquer sur le montant HT
	//                  "AccompteExige" => [0|1],
	//                      "accompte"         => value    // montant de l'acompte (TTC)
	//                      "accompte_percent" => percent  // pourcentage d'acompte (TTC)
	//                  "Remarque" => "texte"              // texte
	// tab_tva = array( "1"       => 19.6,
	//                  "2"       => 5.5, ... );
	// invoice = array( "px_unit" => value,
	//                  "qte"     => qte,
	//                  "tva"     => code_tva );
	function addTVAs($igv, $total, $moneda)
	{
		$this->SetFont('Arial', '', 8);

		$re  = $this->w - 30;
		$rf  = $this->w - 29;
		$y1  = $this->h - 40;
		$this->SetFont("Arial", "", 8);
		$this->SetXY($re, $y1 + 5);
		$this->Cell(17, 4, $moneda . sprintf("%0.2F", $total - ($total * $igv / ($igv + 100))), '', '', 'R');
		$this->SetXY($re, $y1 + 10);
		$this->Cell(17, 4, $moneda . sprintf("%0.2F", ($total * $igv / ($igv + 100))), '', '', 'R');
		$this->SetXY($re, $y1 + 14.8);
		$this->Cell(17, 4, $moneda . sprintf("%0.2F", $total), '', '', 'R');
	}

	// add a watermark (temporary estimate, DUPLICATA...)
	// call this method first
	function temporaire($texte)
	{
		$this->SetFont('Arial', 'B', 50);
		$this->SetTextColor(203, 203, 203);
		$this->Rotate(45, 55, 190);
		$this->Text(55, 190, $texte);
		$this->Rotate(0);
		$this->SetTextColor(0, 0, 0);
	}

	// Colored table
	function FancyTable($header, $data)
	{
		// Colors, line width and bold font
		$this->SetFillColor(176, 100, 100);
		$this->SetTextColor(0, 0, 0);
		$this->SetDrawColor(176, 100, 100);
	}

	/*******************************************************************************
	 *                              Reporte Design                                 *
	 *******************************************************************************/

	function encabezado($y, $logo, $ext_logo, $num_comprobante, $tipo_comprobante, $local, $local_ruc, $estado, $usuario, $content1, $empresa, $content2)
	{
		# LOGO #
		$this->Image('../files/logo_reportes/' . $logo, 15, 10, 30, 30, $ext_logo);

		# EMPRESA #
		$this->SetXY(13.5, $y + 41);
		$this->SetFont('Arial', 'B', 12);
		$this->SetTextColor(0, 0, 0);
		$this->MultiCell(0, 5, mb_convert_encoding(mb_strtoupper("$empresa"), 'ISO-8859-1', 'UTF-8'), 0, 'L', false);

		# DATOS DE LA EMPRESA #
		$this->SetXY(13.5, $y + 47.5);
		$this->SetFont('Arial', '', 9);
		$this->MultiCell(130, 4, mb_convert_encoding($content2, 'ISO-8859-1', 'UTF-8'), 0, 'L', false);

		# CAJA PARA TIPO COMPROBANTE, NUMERO COMPROBANTE Y RUC #
		$this->SetFillColor(242, 209, 77);
		$this->SetTextColor(0, 0, 0);
		$this->SetDrawColor(255, 255, 255);
		$this->RoundedRect(110, 10, 85, 18, 2.5, 'DF');
		$this->SetDrawColor(255, 255, 255);
		$this->SetLineWidth(1);
		$this->Line(110, 19.5, 195, 19.5);
		$this->SetDrawColor(255, 255, 255);
		$this->SetLineWidth(1);
		$this->Line(152, 20, 152, 28);

		# TIPO DE COMPROBANTE #
		$this->SetXY(106, $y + 10.5);
		$this->SetFont('Arial', 'B', 11);
		$this->SetTextColor(0, 0, 0);
		$this->MultiCell(0, 5, mb_convert_encoding("$tipo_comprobante", 'ISO-8859-1', 'UTF-8'), 0, 'C', false);

		# RUC #
		$this->SetXY(62, $y + 19.5);
		$this->SetFont('Arial', 'B', 10);
		$this->SetTextColor(0, 0, 0);
		$this->MultiCell(0, 5, mb_convert_encoding("RUC: $local_ruc", 'ISO-8859-1', 'UTF-8'), 0, 'C', false);

		# NÚMERO DE COMPROBANTE #
		$r1  = $this->w - 65;
		$r2  = $r1 + 38;
		$y1  = 6;
		$y2  = $y1 + 2;

		$szfont = 10;
		$loop   = 0;

		$num_comprobante = mb_convert_encoding("N° $num_comprobante", 'ISO-8859-1', 'UTF-8');

		while ($loop == 0) {
			$this->SetFont("Arial", "B", $szfont);
			$sz = $this->GetStringWidth($num_comprobante);
			if (($r1 + $sz) > $r2)
				$szfont--;
			else
				$loop++;
		}

		$this->SetXY($r1 + 10, $y1 + 15.5);
		$this->Cell($r2 - $r1 - 1, 5, $num_comprobante, 0, 0, "C");

		# ESTADO #
		$this->SetXY(66, $y + 32);
		$this->SetFont('Arial', 'B', 9);
		$this->MultiCell(130, 4, mb_convert_encoding(mb_strtoupper("Estado: $estado"), 'ISO-8859-1', 'UTF-8'), 0, 'R', false);

		# USUARIO #
		$this->SetXY(66, $y + 41);
		$this->SetFont('Arial', 'B', 12);
		$this->SetTextColor(0, 0, 0);
		$this->MultiCell(130, 4, mb_convert_encoding(mb_strtoupper("Cajero: $usuario"), 'ISO-8859-1', 'UTF-8'), 0, 'R', false);

		# DATOS DEL USUARIO #
		$this->SetXY(66, $y + 47.5);
		$this->SetFont('Arial', '', 9);
		$this->SetTextColor(0, 0, 0);
		$this->MultiCell(130, 4, mb_convert_encoding("$content1", 'ISO-8859-1', 'UTF-8'), 0, 'R', false);
	}

	function proveedor($y, $proveedor, $telefono, $tipo_documento, $num_documento, $fecha_hora, $impuesto, $comentario_externo)
	{
		# TITULO PROVEEDOR #
		$this->SetXY(13.5, $y + 3.5);
		$this->SetFont('Arial', 'B', 12);
		$this->SetTextColor(0, 0, 0);
		$this->MultiCell(0, 5, mb_convert_encoding(mb_strtoupper("PROVEEDOR"), 'ISO-8859-1', 'UTF-8'), 0, 'L', false);

		# PROVEEDOR #
		$this->SetXY(13.5, $y += 10);
		$this->SetFont('Arial', 'B', 10);
		$this->SetTextColor(0, 0, 0);
		$this->MultiCell(0, 5, mb_convert_encoding("Nombre:", 'ISO-8859-1', 'UTF-8'), 0, 'L', false);

		$this->SetXY(33, $y);
		$this->SetFont('Arial', '', 10);
		$this->SetTextColor(0, 0, 0);
		$this->MultiCell(0, 5, mb_convert_encoding(mb_strtoupper($proveedor), 'ISO-8859-1', 'UTF-8'), 0, 'L', false);

		# TELEFONO #
		$this->SetXY(13.5, $y += 5);
		$this->SetFont('Arial', 'B', 10);
		$this->SetTextColor(0, 0, 0);
		$this->MultiCell(0, 5, mb_convert_encoding("Teléfono:", 'ISO-8859-1', 'UTF-8'), 0, 'L', false);

		$this->SetXY(33, $y);
		$this->SetFont('Arial', '', 10);
		$this->SetTextColor(0, 0, 0);
		$this->MultiCell(0, 5, mb_convert_encoding(($telefono != "") ? $telefono : "Sin registrar.", 'ISO-8859-1', 'UTF-8'), 0, 'L', false);

		# TIPO DOCUMENTO Y N° DOCUMENTO #
		if ($tipo_documento != "") {
			$this->SetXY(13.5, $y += 5);
			$this->SetFont('Arial', 'B', 10);
			$this->SetTextColor(0, 0, 0);
			$this->MultiCell(0, 5, mb_convert_encoding("$tipo_documento:", 'ISO-8859-1', 'UTF-8'), 0, 'L', false);

			if ($tipo_documento == "CARNET DE EXTRANJERIA") {
				$this->SetXY(62, $y);
				$this->SetFont('Arial', '', 10);
				$this->SetTextColor(0, 0, 0);
				$this->MultiCell(0, 5, mb_convert_encoding(mb_strtoupper($num_documento), 'ISO-8859-1', 'UTF-8'), 0, 'L', false);
			} else {
				$this->SetXY(33, $y);
				$this->SetFont('Arial', '', 10);
				$this->SetTextColor(0, 0, 0);
				$this->MultiCell(0, 5, mb_convert_encoding(mb_strtoupper($num_documento), 'ISO-8859-1', 'UTF-8'), 0, 'L', false);
			}
		} else {
			$this->SetXY(13.5, $y += 5);
			$this->SetFont('Arial', '', 10);
			$this->SetTextColor(0, 0, 0);
			$this->MultiCell(0, 5, mb_convert_encoding("N° y tipo de documento sin registrar.", 'ISO-8859-1', 'UTF-8'), 0, 'L', false);
		}

		# FECHA Y HORA #
		$this->SetXY(123, $y - 10);
		$this->SetFont('Arial', 'B', 10);
		$this->SetTextColor(0, 0, 0);
		$this->MultiCell(0, 5, mb_convert_encoding("Fecha y hora:", 'ISO-8859-1', 'UTF-8'), 0, 'L', false);

		$this->SetXY(150, $y - 10);
		$this->SetFont('Arial', '', 10);
		$this->SetTextColor(0, 0, 0);
		$this->MultiCell(0, 5, mb_convert_encoding(mb_strtoupper($fecha_hora), 'ISO-8859-1', 'UTF-8'), 0, 'L', false);

		# MONEDA #
		$this->SetXY(123, $y - 5);
		$this->SetFont('Arial', 'B', 10);
		$this->SetTextColor(0, 0, 0);
		$this->MultiCell(0, 5, mb_convert_encoding("Moneda:", 'ISO-8859-1', 'UTF-8'), 0, 'L', false);

		$this->SetXY(150, $y - 5);
		$this->SetFont('Arial', '', 10);
		$this->SetTextColor(0, 0, 0);
		$this->MultiCell(0, 5, mb_convert_encoding(mb_strtoupper("S/."), 'ISO-8859-1', 'UTF-8'), 0, 'L', false);

		# IGV #
		$this->SetXY(123, $y);
		$this->SetFont('Arial', 'B', 10);
		$this->SetTextColor(0, 0, 0);
		$this->MultiCell(0, 5, mb_convert_encoding("IGV:", 'ISO-8859-1', 'UTF-8'), 0, 'L', false);

		$this->SetXY(150, $y);
		$this->SetFont('Arial', '', 10);
		$this->SetTextColor(0, 0, 0);
		$this->MultiCell(0, 5, mb_convert_encoding(mb_strtoupper(($impuesto == '0.00' || $impuesto == '') ? ('0.00%') : ('0.18%')), 'ISO-8859-1', 'UTF-8'), 0, 'L', false);

		# COMENTARIO EXTERNO #
		$anchoCaja = 100;
		$palabras = explode(' ', $comentario_externo);

		$anchoAcumulado = 0;
		$lineas = 1; // Inicializamos en 1 ya que siempre tendremos al menos una línea

		if ($comentario_externo != "") {
			foreach ($palabras as $palabra) {
				$anchoPalabra = $this->GetStringWidth($palabra . ' ');

				if ($anchoAcumulado + $anchoPalabra > $anchoCaja) {
					$lineas++; // Incrementamos el contador de líneas
					$anchoAcumulado = $anchoPalabra; // Reiniciamos el ancho acumulado con la nueva palabra
				} else {
					$anchoAcumulado += $anchoPalabra; // Añadimos el ancho de la palabra al acumulado
				}
			}

			$this->SetXY(13.5, $y + 7);
			$this->SetFont('Arial', '', 10);
			$this->MultiCell(100, 5, mb_convert_encoding($comentario_externo, 'ISO-8859-1', 'UTF-8'), 0, 'L', false);

			$y += ($lineas * 5) + 10;
		} else {
			$y += $lineas + 8;
		}

		return $y;
	}

	function cuerpo($y, $totalProductos, $totalUnidades, $textoEnMayusculas)
	{
		# TOTAL CANTIDAD PRODUCTOS #
		$this->SetXY(50, $y);
		$this->SetFont('Arial', 'B', 9);
		$this->SetTextColor(0, 0, 0);
		$this->MultiCell(50, 3.5, mb_convert_encoding(mb_strtoupper(" Total de productos: $totalProductos"), 'ISO-8859-1', 'UTF-8'), 0, 'C', false);

		$this->Ln(3);

		# TOTAL UNIDADES PRODUCTOS #
		$this->SetXY(116, $y);
		$this->SetFont('Arial', 'B', 9);
		$this->SetTextColor(0, 0, 0);
		$this->MultiCell(50, 3.5, mb_convert_encoding(mb_strtoupper("Total de unidades: $totalUnidades"), 'ISO-8859-1', 'UTF-8'), 0, 'C', false);

		# TOTAL COMPRA EN LETRA #
		$this->SetXY(15, $y += 6);
		$this->SetFont('Arial', 'B', 9);
		$this->SetTextColor(0, 0, 0);
		$this->MultiCell(0, 3.5, mb_convert_encoding(mb_strtoupper("Son: $textoEnMayusculas"), 'ISO-8859-1', 'UTF-8'), 0, 'C', false);

		return $y;
	}

	function creditosReporte($y, $tipo_comprobante, $auspiciado)
	{
		# TÍTULO REPRESENTACIÓN #
		$this->SetXY(20, $y += 3);
		$this->SetFont('Arial', '', 8);
		$this->MultiCell(80, 3, mb_convert_encoding(mb_strtoupper("Representación interna de"), 'ISO-8859-1', 'UTF-8'), 0, 'R', false);

		# TIPO DE COMPROBANTE #
		$this->SetXY(20, $y += 4.5);
		$this->SetFont('Arial', 'B', 8);
		$this->MultiCell(80, 3, mb_convert_encoding(mb_strtoupper("$tipo_comprobante"), 'ISO-8859-1', 'UTF-8'), 0, 'R', false);

		# POWERED BY #
		$this->SetXY(13.5, $y += 34);
		$this->SetFont('Arial', 'I', 8);
		$this->MultiCell(0, 3, mb_convert_encoding(("Powered by $auspiciado"), 'ISO-8859-1', 'UTF-8'), 0, 'L', false);
	}
}
