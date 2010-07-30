<?php


include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'anthologize' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . 'tcpdf' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'eng.php');
include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'anthologize' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . 'tcpdf' . DIRECTORY_SEPARATOR . 'tcpdf.php');
include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'anthologize' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom.php');

class TeiPdf {

	public $tei;
	public $pdf;

	function __construct($tei_dom) {

		// Creates an object of type DOMDocument
		// and exposes it as the attribute $tei
		//$this->tei = $tei_dom->getTeiDom();
    $this->tei = new DOMDocument();

    $this->tei->loadXML($tei_dom->getTeiString());

		$this->pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	}

	public function write_pdf() {

		//set auto page breaks
		$this->pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		//set image scale factor
		$this->pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		//set some language-dependent strings
		$this->pdf->setLanguageArray($l);

		$this->set_docinfo();
		$this->set_font();
		$this->set_margins();

		// ---------------------------------------------------------

		// set default font subsetting mode
		$this->pdf->setFontSubsetting(true);

		// Set font
		// dejavusans is a UTF-8 Unicode font, if you only need to
		// print standard ASCII chars, you can use core fonts like
		// helvetica or times to reduce file size.
		$this->pdf->SetFont('times', '', 12, '', true);

		// Add a page
		// This method has several options, check the source code documentation
		// for more information.
		$this->pdf->AddPage();

		// Set some content to print

		$xpath = new DOMXpath($this->tei);
		$xpath->registerNamespace('tei', TEI);
		$xpath->registerNamespace('html', HTML);
		
		// Create a nodeList containing all parts.
		$parts = $xpath->query("//tei:div[@type='part']");

		foreach ($parts as $part) {
			// Grab the main title for each part and render it as
			// a "chapter" title.
			$title = $xpath->query("tei:head/tei:title", $part)->item(0);
			$html = $html . "<h1>" . $title->textContent . "</h1>";

			// Create a nodeList containing all libraryItems
			$library_items = $xpath->query("//tei:div[@type='libraryItem']", $part);

			foreach ($library_items as $item) {
				// Grab the main title for each libraryItem and render it
				// as a "sub section" title.
				$sub_title = $xpath->query("tei:head/tei:title", $item)->item(0);
				$html = $html . "<h3>" . $sub_title->textContent . "</h3>";

				// Grab all paragraphs
				$paras = $xpath->query("//html:p", $item);

				foreach ($paras as $para) {
					$html = $html . $this->strip_whitespace($this->node_to_string($para));
				} // foreach para

			} // foreach item

			$this->pdf->WriteHTML($html, false, true, true, false, "L");

		} // foreach part

		// Close and output PDF document
		// This method has several options, check the source code
		// documentation for more information.

		// echo $html; // DEBUG
		$this->pdf->Output('example_001.pdf', 'I');

	} // writePDF 

	public function set_header() {

		// set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING);

	}

	public function set_footer() {

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	}

	public function set_docinfo() {

		$this->pdf->SetCreator(PDF_CREATOR);
		$this->pdf->SetAuthor('Boone Gorges');
		$this->pdf->SetTitle('The Book of Boone');
		$this->pdf->SetSubject('Barbecue');
		$this->pdf->SetKeywords('Boone, barbecue, oneweek');

	}

	public function set_font() {

		// set default monospaced font
		$this->pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	}

	public function set_margins() {

		//set margins
		$this->pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$this->pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$this->pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	}

	private function node_to_string($node) {
		return $this->tei->saveXML($node);
	}

	private function strip_whitespace($string) {
		return preg_replace('/\s+/', ' ', $string);
	}


} // TeiPdf
?>
