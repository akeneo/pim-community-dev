<?php

namespace Pim\Bundle\VersioningBundle\Purger;

use Akeneo\Tool\Component\Versioning\Model\VersionInterface;
use Pim\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;

/**
 * Prevents last version of an entity from being purged
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SkipLastVersionPurgerAdvisor implements VersionPurgerAdvisorInterface
{
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
    public function supports(VersionInterface $version)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isPurgeable(VersionInterface $version, array $options)
    {
        $newVersionId = $this->versionRepository->getNewestVersionIdForResource(
            $version->getResourceName(),
            $version->getResourceId()
        );

        return null === $newVersionId || $newVersionId !== $version->getId();
    }
}
