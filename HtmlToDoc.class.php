<?php
/**
 * Utility class for converting to a Word Document
 *
 * @since 1.0.0
 * @package learndash_notes
 */

namespace learndash_student_notes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Utility class for converting to a Word Document
 *
 * @since 1.0.0
 */
class HTML_TO_DOC {

	/**
	 * Document Filename
	 *
	 * @var string
	 */
	public $doc_file = '';

	/**
	 * Document Title
	 *
	 * @var string
	 */
	public $title = '';

	/**
	 * HTML Head
	 *
	 * @var string
	 */
	public $html_head = '';

	/**
	 * HTML Body
	 *
	 * @var string
	 */
	public $html_body = '';

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {
		$this->title     = '';
		$this->html_head = '';
		$this->html_body = '';
	}

	/**
	 * Set the document file name
	 *
	 * @param   string $doc_file  Document Filename.
	 *
	 * @return  void
	 */
	private function set_doc_filename( $doc_file ) {
		$this->doc_file = $doc_file;
		if ( ! preg_match( '/\.doc$/i', $this->doc_file ) && ! preg_match( '/\.docx$/i', $this->doc_file ) ) {
			$this->doc_file .= '.doc';
		}
	}

	/**
	 * Set the document title
	 *
	 * @param string $title Document title.
	 */
	private function set_title( $title ) {
		$this->title = esc_html( $title );
	}

	/**
	 * Return header of MS Doc
	 *
	 * @return String
	 */
	private function get_header() {
		$return = <<<EOH
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
        <title>$this->title</title> 
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
        </head> 
        <body> 
EOH;
		return $return;
	}

	/**
	 * Return Document footer
	 *
	 * @return String
	 */
	private function get_footer() {
		return '</body></html>';
	}

	/**
	 * Create The MS Word Document from given HTML
	 *
	 * @param String $html :: HTML Content or HTML File Name like path/to/html/file.html.
	 * @param String $file :: Document File Name.
	 * @return void
	 */
	public function create_doc( $html, $file ) {
		if ( is_file( $html ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$html = file_get_contents( $html );
		}

		$this->parse_html( $html );
		$this->set_doc_filename( $file );
		$doc  = $this->get_header();
		$doc .= wp_kses_post( $this->html_body );
		$doc .= $this->get_footer();

		header( 'Cache-Control: ' );// leave blank to avoid IE errors.
		header( 'Pragma: ' );// leave blank to avoid IE errors.
		header( 'Content-type: application/octet-stream' );
		header( "Content-Disposition: attachment; filename=\"$this->doc_file\"" );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $doc;
	}

	/**
	 * Parse the html and remove <head></head> part if present into html
	 *
	 * @param String $html Document Body HTML.
	 * @return void
	 * @access private
	 */
	private function parse_html( $html ) {
		$html = preg_replace( "/<!DOCTYPE((.|\n)*?)>/ims", '', $html );
		$html = preg_replace( "/<script((.|\n)*?)>((.|\n)*?)<\/script>/ims", '', $html );
		preg_match( "/<head>((.|\n)*?)<\/head>/ims", $html, $matches );
		$head = ! empty( $matches[1] ) ? $matches[1] : '';
		preg_match( "/<title>((.|\n)*?)<\/title>/ims", $head, $matches );
		$this->title     = ! empty( $matches[1] ) ? esc_html( $matches[1] ) : '';
		$html            = preg_replace( "/<head>((.|\n)*?)<\/head>/ims", '', $html );
		$html            = preg_replace( "/<\/?body((.|\n)*?)>/ims", '', $html );
		$this->html_body = $html;
	}
}
