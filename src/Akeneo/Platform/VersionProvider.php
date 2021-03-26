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
    private string $edition;
    private string $version;
    private string $codeName;

    public function __construct(string $versionClass, ?string $edition = null)
    {
        $this->version = constant(sprintf('%s::VERSION', $versionClass));
        $this->edition = $edition ?? constant(sprintf('%s::EDITION', $versionClass));
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
            $isMatching = preg_match('/^(?P<patch>\d+.\d+.\d+)/', $this->version, $matches);

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
            $isMatching = preg_match('/^(?P<minor>\d+.\d+).\d+/', $this->version, $matches);

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
        return sprintf('%s %s %s', $this->edition, $this->version, $this->codeName);
    }

    /**
     * {@inheritdoc}
     */
    public function isSaaSVersion(): bool
    {
        return false !== strpos(strtolower($this->edition), 'saas');
    }
}
