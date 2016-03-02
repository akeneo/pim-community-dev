<?php

namespace Pim\Bundle\EnrichBundle\Provider\StructureVersion;

use Pim\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;

/**
 * Product structure version provider
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductStructureVersionProvider implements StructureVersionProviderInterface
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
        return $this->versionRepository
            ->getNewestLogEntryForRessources($this->resourceNames)
            ->getLoggedAt()
            ->getTimestamp();
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
