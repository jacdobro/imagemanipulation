<?php

namespace imagemanipulation;

use imagemanipulation\color\Color;

use imagemanipulation\ImageImageResource;
use imagemanipulation\filter\ImageFilterBrightness;
use imagemanipulation\filter\ImageFilterColorize;
use imagemanipulation\color\ColorUtil;
use imagemanipulation\filter\ImageFilterContrast;
use imagemanipulation\filter\ImageFilterDarken;
use imagemanipulation\filter\ImageFilterDodge;
use imagemanipulation\filter\ImageFilterEdgeDetect;
use imagemanipulation\filter\ImageFilterSobelEdgeDetect;
use imagemanipulation\filter\ImageFilterEmboss;
use imagemanipulation\filter\ImageFilterFlip;
use imagemanipulation\filter\ImageFilterFindEdges;
use imagemanipulation\filter\ImageFilterGaussianBlur;
use imagemanipulation\filter\ImageFilterGrayScale;
use imagemanipulation\filter\ImageFilterMeanRemove;
use imagemanipulation\filter\ImageFilterNegative;
use imagemanipulation\filter\ImageFilterNoise;
use imagemanipulation\filter\ImageFilterOpacity;
use imagemanipulation\filter\ImageFilterPixelate;
use imagemanipulation\filter\ImageFilterReplaceColor;
use imagemanipulation\filter\ImageFilterScatter;
use imagemanipulation\filter\ImageFilterSelectiveBlur;
use imagemanipulation\filter\ImageFilterSepia;
use imagemanipulation\filter\ImageFilterSepiaFast;
use imagemanipulation\filter\ImageFilterSharpen;
use imagemanipulation\filter\ImageFilterSmooth;
use imagemanipulation\filter\ImageFilterRandomBlocks;
use imagemanipulation\rotate\ImageFilterRotate;
use imagemanipulation\watermark\WatermarkBuilder;
use imagemanipulation\filter\ImageFilterVignette;
/*
 * TODO checkout https://github.com/marchibbins/GD-Filter-testing
 */
class ImageBuilder {
	
	private $res;
	private $queue;
	
	public function __construct(\SplFileInfo $aImage){
		$this->res = new ImageImageResource($aImage);
		$this->queue = new \ArrayObject();
	}
	
	public static function create(\SplFileInfo $aImage){
		return new ImageBuilder($aImage);
	}
	
	
	public function brightness($aRate = 20){
		$this->queue->append(new ImageFilterBrightness($aRate));
		return $this;
	}
	
	public function colorize($aColor = 'FFFFFF', $aAlpha = null){
		$color = new Color($aColor, $aAlpha);
		
		$this->queue->append(new ImageFilterColorize($color->getRed(), $color->getGreen(), $color->getBlue(), $color->getAlpha()));
		return $this;
	}
	
	public function contrast($aRate = 5){
		$this->queue->append(new ImageFilterContrast($aRate));
		return $this;
	}
	public function darken($aRate = 5){
		$this->queue->append(new ImageFilterDarken($aRate));
		return $this;
	}
	public function dodge($aRate = 50){
		$this->queue->append(new ImageFilterDodge($aRate));
		return $this;
	}
	public function edgeDetect( ){
		$this->queue->append(new ImageFilterEdgeDetect());
		return $this;
	}
	public function sobelEdgeDetect( ){
		$this->queue->append(new ImageFilterSobelEdgeDetect());
		return $this;
	}
	public function emboss( ){
		$this->queue->append(new ImageFilterEmboss());
		return $this;
	}
	public function flip( $aFlip = ImageFilterFlip::FLIP_HORIZONTALLY ){
		$horizontally = $aFlip == ImageFilterFlip::FLIP_HORIZONTALLY || $aFlip ==  ImageFilterFlip::FLIP_BOTH;
		$vertically =  $aFlip == ImageFilterFlip::FLIP_VERTICALLY || $aFlip ==  ImageFilterFlip::FLIP_BOTH;
		$this->queue->append(new ImageFilterFlip($horizontally, $vertically));
		return $this;
	}
	public function findEdges( ){
		$this->queue->append(new ImageFilterFindEdges());
		return $this;
	}
	public function gaussianBlur( ){
		$this->queue->append(new ImageFilterGaussianBlur());
		return $this;
	}
	public function grayscale( ){
		$this->queue->append(new ImageFilterGrayScale());
		return $this;
	}
	public function meanremove( ){
		$this->queue->append(new ImageFilterMeanRemove());
		return $this;
	}
	public function negative( ){
		$this->queue->append(new ImageFilterNegative());
		return $this;
	}
	// TODO defaults should come from constants from filters
	public function noise( $aRate = 20){
		$this->queue->append(new ImageFilterNoise($aRate));
		return $this;
	}
	public function opacity( $aRate = 50){
		$this->queue->append(new ImageFilterOpacity($aRate));
		return $this;
	}
	public function pixelate( $aRate = 10){
		$this->queue->append(new ImageFilterPixelate($aRate));
		return $this;
	}
	public function randomBlocks($aNumberOfBlocks = 100, $aBlockSize = 25, $aBlockColor = 'FFFFFF'){
		$this->queue->append(new ImageFilterRandomBlocks($aNumberOfBlocks, $aBlockSize, $aBlockColor));
		return $this;
	}
	public function replace( $aSearches, $aReplace){
		$this->queue->append(new ImageFilterReplaceColor($aSearches, $aReplace));
		return $this;
	}
	public function rotate( $aDegrees = 90, $aBgColor = null){
		$this->queue->append(new ImageFilterRotate($aDegrees, $aBgColor));
		return $this;
	}
	public function scatter( $aOffset = 4){
		$this->queue->append(new ImageFilterScatter($aOffset));
		return $this;
	}
	public function selectiveBlur(){
		$this->queue->append(new ImageFilterSelectiveBlur());
		return $this;
	}
	public function sepia($aDarken = 15){
		$this->queue->append(new ImageFilterSepia($aDarken));
		return $this;
	}
	public function sepiaFast(){
		$this->queue->append(new ImageFilterSepiaFast());
		return $this;
	}
	public function sharpen(){
		$this->queue->append(new ImageFilterSharpen());
		return $this;
	}
	public function smooth($aRate = 5){
		$this->queue->append(new ImageFilterSmooth($aRate));
		return $this;
	}
	public function vignette(){
		$this->queue->append(new ImageFilterVignette());
		return $this;
	}
	
	public function render($aQuality = null){
		if ($aQuality != null){
			$this->res->setQuality($aQuality);
		}
		
		$this->applyFilters();
		$this->res->outputImage();
	}
	
	public function save(\SplFileInfo $aFile, $aOverwrite = false){
		$this->applyFilters();
		
		$this->res->setIsOverwrite($aOverwrite);
		$this->res->setOutputPath($aFile);
		$this->res->createImage();
	}
	
	public function watermarkBuilder(){
		$builder = new WatermarkBuilder();
		$this->queue->append($builder);
		return $builder;
	}
	
	private function applyFilters(){
		if ($this->queue->count() > 0){
			/* @var $filter \imagemanipulation\filter\IImageFilter */
			foreach ($this->queue as $filter){
				$filter->applyFilter($this->res);
			}
			
			$this->queue = new \ArrayObject();
		}
	}
}