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
    private PimEdition $edition;
    private string $version;
    private string $codeName;

    public function __construct(string $versionClass)
    {
        $this->edition = PimEdition::fromString(constant(sprintf('%s::EDITION', $versionClass)));
        $this->version = constant(sprintf('%s::VERSION', $versionClass));
        $this->codeName = constant(sprintf('%s::VERSION_CODENAME', $versionClass));
    }

    /**
     * {@inheritdoc}
     */
    public function getEdition(): string
    {
        return $this->edition->asString();
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
        if (!$this->isSaaSVersion()) {
            $matches = [];
            $isMatching = preg_match('/^(?P<patch>\d+\.\d+\.\d+)/', $this->version, $matches);

            if (!$isMatching) {
                return $this->version;
            }

            return $matches['patch'];
        }

        return $this->version;
    }

    public function getMinorVersion(): string
    {
        if (!$this->isSaaSVersion()) {
            $matches = [];
            $isMatching = preg_match('/^(?P<minor>\d+\.\d+)\.\d+/', $this->version, $matches);

            if (!$isMatching) {
                return $this->version;
            }

            return $matches['minor'];
        }

        return $this->version;
    }

    /**
     * {@inheritdoc}
     */
    public function getFullVersion(): string
    {
        return sprintf('%s %s %s', $this->edition->asString(), $this->version, $this->codeName);
    }

    /**
     * {@inheritdoc}
     */
    public function isSaaSVersion(): bool
    {
        return $this->edition->isSaasVersion();
    }
}
