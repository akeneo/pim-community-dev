<?php

namespace Pim\Bundle\EnrichBundle\Provider\StructureVersion;

use Pim\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;

/**
 * Structure version provider
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StructureVersionProvider implements StructureVersionProviderInterface
{
    /** @var array */
    protected $resourceNames = [];

    /** @var VersionRepositoryInterface */
    protected $versionRepository;

    /**
     * @param VersionRepositoryInterface $versionRepository
     */
    public function __construct(VersionRepositoryInterface $versionRepository)
    {
        $this->versionRepository = $versionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getStructureVersion()
    {
        $latest = $this->versionRepository->getNewestLogEntryForRessources($this->resourceNames);

        if (null === $latest) {
            return null;
        }

        return $latest->getLoggedAt()->getTimestamp();
    }

    /**
     * Add a resource name to the structure
     *
     * @param string $resourceName
     */
    public function addResource($resourceName)
    {
        if (!in_array($resourceName, $this->resourceNames)) {
            $this->resourceNames[] = $resourceName;
        }
    }
}
