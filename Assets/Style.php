<?php
namespace MyPlugin\Sci\Assets;

defined('WPINC') OR exit('No direct script access allowed');

use Exception;
use MyPlugin\Sci\Asset;
use MyPlugin\Sci\Manager\StyleManager;

/**
 * Asset
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2020 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.Sci.com 
 * @since		Version 1.0.0 
 */
 
class Style extends Asset
{
    /** @var string $media Media value for ccss files */
    protected $media;

    /** @var StyleManager $styleManager The Style Manager */
	protected $styleManager;

    /**
     * Create a new style
     *
     * @param string $handle The style handle
     * @param string $src The css file location
     * @param string $version The css file asset version
     * @param string[]  $dependences The registered css file dependences
     * @param string $media The asset version
     */
    public function __construct($src, $version = false, $dependences = [], $media = 'all')
    {
        parent::__construct($src, $version, $dependences);
        $this->styleManager = $this->sci::make(StyleManager::class);
        $this->media = $media;
    }

	/**
	 * Add a new style
	 *
     * @param string $src The css file location
     * @param string $version The css file asset version
     * @param string[]  $dependences The registered css file dependences
     * @param string $media The asset version
	 * @return MyPlugin\Sci\Assets\Style
	 */
    public static function create($src, $version = false, $dependences = [], $media = 'all')
    {
        $style = new self($src, $version, $dependences, $media);
        return  $style;
    }

    /**
     * Add the asset to the asset manager
     * 
     * @param string $handle The style handle
     * @param string $zone Frontend or admin panel
     * @return MyPlugin\Sci\Assets\Style
     */
    public function register($handle, $zone = false)
    {
       $this->styleManager->register($this, $handle, $zone);
        return $this;
    }

    /**
     * Returns if the asset media value for css files
     *
     * @return string
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Sets the media value for css files
     *
     * @param string $media The asset media value
     * @return MyPlugin\Sci\Assets\Style
     */
    public function setMedia($media)
    {
        $this->media = $media;
        return $this;
    }
}