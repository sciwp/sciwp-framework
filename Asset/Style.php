<?php
namespace Sci\Asset;

defined('WPINC') OR exit('No direct script access allowed');

use Exception;
use Sci\Sci;
use Sci\Asset\Asset;
use Sci\Asset\Managers\StyleManager;

/**
 * Style
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		https://opensource.org/licenses/LGPL-2.1  GNU Lesser GPL version 2.1
 * @version     1.0.0
 * @link		https://www.sciwp.com
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
     * @param string[]  $dependencies The registered css file dependencies
     * @param string $media The asset version
     */
    public function __construct($src, $version = false, $dependencies = [], $media = 'all', StyleManager $styleManager)
    {
        parent::__construct($src, $version, $dependencies);
        $this->styleManager = $styleManager;
        $this->media = $media;
    }

	/**
	 * Add a new style
	 *
     * @param string|array $src The css file location
     * @param string $version The css file asset version
     * @param string[]  $dependencies The registered css file dependencies
     * @param string $media The asset version
	 * @return Style
	 */
    public static function create($src, $version = false, $dependencies = [], $media = 'all')
    {
        $style = Sci::make(self::class, [$src, $version, $dependencies, $media]);
        return  $style;
    }

    /**
     * Add the asset to the asset manager
     * 
     * @param string $handle The style handle
     * @param string $zone Frontend or admin panel
     * @return Style
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
     * @return Style
     */
    public function setMedia($media)
    {
        $this->media = $media;
        return $this;
    }
}