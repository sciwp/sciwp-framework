<?php
namespace MyPlugin\Sci;

defined('WPINC') OR exit('No direct script access allowed');

use Exception;
use \MyPlugin\Sci\Sci;
use \MyPlugin\Sci\Plugin;
use \MyPlugin\Sci\Manager\AssetManager;

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
 
class Asset
{
    /** @var $sci The Sci class reference */
    protected $sci;

	/** @var string $src The asset absolute path */
    protected $src;

	/** @var string $version The asset version */
    protected $version;

	/** @var string[] $dependences The asset dependences */
    protected $dependences;

    /**
     * Create a new asset
     *
     * @param string $src The asset location
     * @param string $version The asset version
     * @param string[]  $dependences The registered css file dependences
     */
    public function __construct($src, $version = false, $dependences = [])
    {
        $this->src = $src;
        $this->version = $version;
        $this->dependences = (array) $dependences;
        $this->sci = Sci::class;
    }

	/**
	 * Add a new asset
	 *
     * @param string $src The asset location
     * @param string $version The asset version
     * @param array $dependences The asset dependences
	 * @return MyPlugin\Sci\Asset
	 */
    public static function create($src, $version = false, $dependences = [])
    {
        $asset = new self($src,  $version, $dependences);
        return  $asset;
    }

    /**
     * Returns the asset src
     *
     * @return string
     */
    public function getSrc()
    {
        return $this->src;
    }

    /**
     * Returns the asset dependences
     *
     * @return string[]
     */
    public function getDependences()
    {
        return $this->dependences;
    }

    /**
     * Returns the asset version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Sets the asset src
     *
     * @param string $src The asset url
     * @return MyPlugin\Sci\Asset
     */
    public function setSrc($src)
    {
        $this->src = $src;
        return $this;
    }

    /**
     * Sets the asset dependences
     *
     * @param string[] $dependences The asset dependences
     * @return MyPlugin\Sci\Asset
     */
    public function setDependences($dependences)
    {
        $this->dependences = $dependences;
        return $this;
    }

    /**
     * Sets the asset version
     *
     * @param string $version The asset version
     * @return MyPlugin\Sci\Asset
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }
}