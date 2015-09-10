<?php
namespace imagemanipulation\rotate;

use imagemanipulation\color\Color;

use imagemanipulation\color\ColorFactory;
use imagemanipulation\ImageResource;
use imagemanipulation\filter\FilterException;
use imagemanipulation\filter\IImageFilter;
use imagemanipulation\Args;
/**
 * Rotate an image over an angle.
 *
 * @package image
 * @subpackage Imagefilter
 */
class ImageFilterRotate implements IImageFilter
{
	public static $filterType = "rotate";
	
	private $angle;
	
	/**
	 * @var Color
	 */
	private $bgColor;
	
	/**
	 * Creates a new ImageFilterRotate
	 *
	 * @param int $aAngle The degrees to rotate the image
	 * @param String $aBgcolor The background color to apply
	 *
	 */
	public function __construct( $angle = 90, $aBgcolor = null )
	{
	    $this->angle = Args::int($ange, 'angle')->required()->min(-360)->max(360)->value(function($val){
	    	return $val < 0 ? 360 - $val : $val;
	    });
		
		if ($aBgcolor === null)
		{
			$this->bgColor = ColorFactory::white();
		}
		else
		{
			$this->bgColor = $aBgcolor instanceof Color ? $aBgcolor : new Color($aBgcolor);
		}
	
	}
	
	/**
	 * Applies the filter to the image resource
	 *
	 * @param ImageResource $aResource
	 */
	public function applyFilter( ImageResource $aResource )
	{
		if ($this->angle == 0 || $this->angle == 360)
		{
			return;
		}
		
		imageantialias($aResource->getResource(), true);
		
		$aResource->setResource( imagerotate( $aResource->getResource(), $this->angle, $this->bgColor->getColorIndex(), $this->bgColor->getAlpha() ) );
		
		$new_imgres = imagecreatetruecolor( $aResource->getX(), $aResource->getY() );
		$success = imagecopy( $new_imgres, $aResource->getResource(), 0, 0, 0, 0, $aResource->getX(), $aResource->getY() );
		
		if (! $success)
		{
			throw new FilterException( self::$filterType );
		}
		
		imagedestroy( $new_imgres );
	}
}