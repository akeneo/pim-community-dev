<?php


namespace Pim\Bundle\CatalogBundle;

/**
 * Class VersionProvider
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionProvider implements VersionProviderInterface
{
    /** @var string */
    protected $edition;

    /** @var string */
    protected $version;

    /**
     * @param string $versionClass
     */
    public function __construct($versionClass)
    {
        $versionClass  = new \ReflectionClass($versionClass);
        $this->version = $versionClass->getConstant('VERSION');
        $this->edition = $versionClass->getConstant('EDITION');
    }

    /**
     * @return string
     */
    public function getEdition()
    {
        return $this->edition;
    }

    /**
     * @return string
     */
    public function getMajor()
    {
        $matches = [];
        preg_match('/^(?P<major>\d)/', $this->version, $matches);

        return $matches['major'];
    }

    /**
     * @return string
     */
    public function getMinor()
    {
        $matches = [];
        preg_match('/^(?P<minor>\d.\d)/', $this->version, $matches);

        return $matches['minor'];
    }

    /**
     * @return string
     */
    public function getPatch()
    {
        $matches = [];
        preg_match('/^(?P<patch>\d.\d.\d)/', $this->version, $matches);

        return $matches['patch'];
    }

    /**
     * @return string
     */
    public function getStability()
    {
        $matches = [];
        preg_match('/^\d.\d.\d-(?P<stability>\w+)\d$/', $this->version, $matches);

        return (isset($matches['stability'])) ? $matches['stability'] : 'stable';
    }
}
