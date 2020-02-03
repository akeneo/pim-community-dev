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

    /**
     * {@inheritdoc}
     */
    public function getPatch(): string
    {
        if (!$this->isSaaSVersion()) {
            $matches = [];
            preg_match('/^(?P<patch>\d+.\d+.\d+)/', $this->version, $matches);

            return $matches['patch'];
        }

        return $this->version;
    }

    /**
     * {@inheritdoc}
     */
    public function getFullVersion(): string
    {
        return sprintf('%s %s %s', $this->edition, $this->version, $this->codeName);
    }

    /**
     * {@inheritdoc}
     */
    public function isSaaSVersion(): bool
    {
        return false !== strpos(strtolower($this->getEdition()), 'saas');
    }
}
