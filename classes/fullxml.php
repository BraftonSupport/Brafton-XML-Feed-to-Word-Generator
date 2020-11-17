<?php

class FullXML {
	public $url;
	public $idArray;
	public $output;
	public $storage;
	public $htmlHead;
	public function __construct($u, $ids){
		$this->url = $u;
		$this->idArray = $ids;
		$this->htmlHead = "";
		$this->parseFeed($this->url,$this->idArray);
    }
    /**
     * @param $title string
     * @return $return string
     */
	protected function generateHeader(string $title): string {
		$return  = <<<EOH
		 <html xmlns:v="urn:schemas-microsoft-com:vml"
		xmlns:o="urn:schemas-microsoft-com:office:office"
		xmlns:w="urn:schemas-microsoft-com:office:word"
		xmlns="http://www.w3.org/TR/REC-html40">
		
		<head>
		<meta http-equiv=Content-Type content="text/html; charset=utf-8">
		<meta name=ProgId content=Word.Document>
		<meta name=Generator content="Microsoft Word 9">
		<meta name=Originator content="Microsoft Word 9">
		<!--[if !mso]>
		<style>
		v\:* {behavior:url(#default#VML);}
		o\:* {behavior:url(#default#VML);}
		w\:* {behavior:url(#default#VML);}
		.shape {behavior:url(#default#VML);}
		</style>
		<![endif]-->
		<title>$title</title>
		<!--[if gte mso 9]><xml>
		 <w:WordDocument>
			<w:View>Print</w:View>
			<w:DoNotHyphenateCaps/>
			<w:PunctuationKerning/>
			<w:DrawingGridHorizontalSpacing>9.35 pt</w:DrawingGridHorizontalSpacing>
			<w:DrawingGridVerticalSpacing>9.35 pt</w:DrawingGridVerticalSpacing>
		 </w:WordDocument>
		</xml><![endif]-->
		<style>
		<!--
		 /* Font Definitions */
		@font-face
			{font-family:Verdana;
			panose-1:2 11 6 4 3 5 4 4 2 4;
			mso-font-charset:0;
			mso-generic-font-family:swiss;
			mso-font-pitch:variable;
			mso-font-signature:536871559 0 0 0 415 0;}
		 /* Style Definitions */
		p.MsoNormal, li.MsoNormal, div.MsoNormal
			{mso-style-parent:"";
			margin:0in;
			margin-bottom:.0001pt;
			mso-pagination:widow-orphan;
			font-size:7.5pt;
			mso-bidi-font-size:8.0pt;
			font-family:"Verdana";
			mso-fareast-font-family:"Verdana";}
		p.small
			{mso-style-parent:"";
			margin:0in;
			margin-bottom:.0001pt;
			mso-pagination:widow-orphan;
			font-size:1.0pt;
			mso-bidi-font-size:1.0pt;
			font-family:"Verdana";
			mso-fareast-font-family:"Verdana";}
		@page Section1
			{size:8.5in 11.0in;
			margin:1.0in 1.25in 1.0in 1.25in;
			mso-header-margin:.5in;
			mso-footer-margin:.5in;
			mso-paper-source:0;}
		div.Section1
			{page:Section1;}
		-->
		</style>
		<!--[if gte mso 9]><xml>
		 <o:shapedefaults v:ext="edit" spidmax="1032">
			<o:colormenu v:ext="edit" strokecolor="none"/>
		 </o:shapedefaults></xml><![endif]--><!--[if gte mso 9]><xml>
		 <o:shapelayout v:ext="edit">
			<o:idmap v:ext="edit" data="1"/>
		 </o:shapelayout></xml><![endif]-->
		 $this->htmlHead
		</head>
		<body>
EOH;
	return $return;
    }
    /**
     * @param $html string
     * @return $html string
     */
	private function parseHtml(string $html): string {
		$html = preg_replace("/<!DOCTYPE((.|\n)*?)>/ims","", $html);
		$html = preg_replace("/<script((.|\n)*?)>((.|\n)*?)<\/script>/ims", "", $html);
		preg_match("/<head>((.|\n)*?)<\/head>/ims", $html, $matches);
		$head = (array_key_exists(1, $matches)) ? $matches[1] : '';
		preg_match("/<title>((.|\n)*?)<\/title>/ims", $head, $matches);
		//$this->title = $matches[1];
		$html = preg_replace("/<head>((.|\n)*?)<\/head>/ims", "", $html);
		$head = preg_replace("/<title>((.|\n)*?)<\/title>/ims", "", $head);
		$head = preg_replace("/<\/?head>/ims", "", $head);
		$html = preg_replace("/<\/?body((.|\n)*?)>/ims", "", $html);
		//$this->htmlHead = $head;
		//$this->htmlBody = $html;
		return $html;
    }
    /**
     * @param $a string, $b array
     */
	private function parseFeed(string $a, array $b) : void {
		$master = new DOMDocument();
		$counter = 0;
		foreach($b as $i) {
			$counter++;
			$tempUrl = $a.'/'.$i;
			$temp = '';
			$data = simplexml_load_file($tempUrl);
			$photos = simplexml_load_file($tempUrl.'/photos');
			ob_start();
			echo '<h1>'.$data->headline.'</h1>';
			echo '<img src="'.$photos->photo->instances->instance->url[0].'" />';
			echo $data->text;
			$content = ob_get_contents();
			ob_end_clean();
			$entry = $this->generateHeader($data->headline);
			$docx = $this->parseHtml($content);
			$temp .= $entry . $docx."</body></html>";
			$fp = fopen($_SERVER['DOCUMENT_ROOT'] . "/feeds/".$data->id.".doc","wb");
			fwrite($fp,$temp);
			fclose($fp);
		}
		echo $counter .' files written to server.';
	}
}
?>