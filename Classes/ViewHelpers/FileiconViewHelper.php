<?php
class Tx_DrBlob_ViewHelpers_FileiconViewHelper extends Tx_Fluid_ViewHelpers_ImageViewHelper {

	public function initializeArguments() {
		$this->registerUniversalTagAttributes();
		$this->registerTagAttribute('alt', 'string', 'Specifies an alternative folder for the file icons', false );
	}

	/**
	 * Render the img tag for the file extension of a given filename.
	 *
	 * @param string $filename The filename to get the icon for
	 * @param string $folder The folder to pick the icons from
	 * @param string $width width of the image. This can be a numeric value representing the fixed width of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.
	 * @param string $height height of the image. This can be a numeric value representing the fixed height of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.
	 * @param integer $minWidth minimum width of the image
	 * @param integer $minHeight minimum height of the image
	 * @param integer $maxWidth maximum width of the image
	 * @param integer $maxHeight maximum height of the image
	 *
	 * @return string rendered tag.
	 */
	public function render( $filename = NULL, $folder = NULL, $width = NULL, $height = NULL, $minWidth = NULL, $minHeight = NULL, $maxWidth = NULL, $maxHeight = NULL) {
		if( $filename === null ) {
			$filename = $this->renderChildren();
			if( $filename === null ) {
				return '';
			}
		}
		if( $folder === null ) {
			$folder = 'typo3/sysext/cms/tslib/media/fileicons/';
		}
		
		$defaultImg = 'default.gif';
		$extImg = self::getFileExtension( $filename ) . '.gif';
		
		if( file_exists( $folder . $extImg ) ) {
			$src = $folder . $extImg;
			
			if ( $this->arguments['alt'] === '' ) {
				$this->tag->addAttribute( 'alt', self::getFileExtension( $filename ) );
			}
		} else {
			$src = $folder . $defaultImg;
		}
		
		return parent::render( $src, $width, $height, $minWidth, $minHeight, $maxWidth, $maxHeight );
	}
	
	
	/**
	 * Returns the fileextension of the given Filename.
	 * 
	 * @param 	String 	$filename
	 * @return 	String 	Extension
	 * @access 	public
	 * @static 
	 */
	protected static function getFileExtension( $fileName ) {
		if ( !empty( $fileName ) ) {
			$tmp = t3lib_div::split_fileref( $fileName );
			return $tmp['realFileext'];
		} else {
			return '';
		}
	}
}
?>