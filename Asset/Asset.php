<?php
namespace Sci\Asset;

defined('WPINC') OR exit('No direct script access allowed');

use Exception;
use Sci\Sci;
use Sci\Plugin\Plugin;

/**
 * Asset
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.sciwp.com
 * @since		Version 1.0.0 
 */
class Asset
{
	/** @var string $src The asset absolute path */
    protected $src;

	/** @var string $version The asset version */
    protected $version;

	/** @var string[] $dependencies The asset dependencies */
    protected $dependencies;

    /**
     * Create a new asset
     *
     * @param string $src The asset location
     * @param string $version The asset version
     * @param string[]  $dependencies The registered css file dependencies
     */
    public function __construct($src, $version = false, $dependencies = [])
    {
        $this->src = $src;
        $this->version = $version;
        $this->dependencies = (array) $dependencies;
    }

	/**
	 * Add a new asset
	 *
     * @param string $src The asset location
     * @param string $version The asset version
     * @param array $dependencies The asset dependencies
	 * @return Asset
	 */
    public static function create($src, $version = false, $dependencies = [])
    {
        return Sci::make(self::class, [$src,  $version, $dependencies]);
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
     * Returns the asset dependencies
     *
     * @return string[]
     */
    public function getDependences()
    {
        return $this->dependencies;
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
     * @return Asset
     */
    public function setSrc($src)
    {
        $this->src = $src;
        return $this;
    }

    /**
     * Sets the asset dependencies
     *
     * @param string[] $dependencies The asset dependencies
     * @return Asset
     */
    public function setDependences($dependencies)
    {
        $this->dependencies = $dependencies;
        return $this;
    }

    /**
     * Sets the asset version
     *
     * @param string $version The asset version
     * @return Asset
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }
}