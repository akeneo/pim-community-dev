<?php

declare(strict_types=1);

namespace Akeneo\Platform;

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
    private $edition;

    /** @var string */
    private $version;

    /** @var string */
    private $codeName;

    /**
     * @param string $versionClass
     */
    public function __construct(string $versionClass)
    {
        $this->version = constant(sprintf('%s::VERSION', $versionClass));
        $this->edition = constant(sprintf('%s::EDITION', $versionClass));
        $this->codeName = constant(sprintf('%s::VERSION_CODENAME', $versionClass));
    }

    /**
     * {@inheritdoc}
     */
    public function getEdition(): string
    {
        return $this->edition;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * {@inheritdoc}
     */
    public function getPatch(): string
    {
        $matches = [];
        $isMatching = preg_match('/^(?P<patch>\d+\.\d+\.\d+)/', $this->version, $matches);

        if (!$isMatching) {
            return $this->version;
        }

        return $matches['patch'];
    }

    public function getMinorVersion(): string
    {
        $matches = [];
        $isMatching = preg_match('/^(?P<minor>\d+\.\d+)\.\d+/', $this->version, $matches);

        if (!$isMatching) {
            return $this->version;
        }

        return $matches['minor'];
    }

    /**
     * {@inheritdoc}
     */
    public function getFullVersion(): string
    {
        return sprintf('%s %s %s', $this->edition, $this->version, $this->codeName);
    }
}
