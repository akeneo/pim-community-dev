<?php

namespace Pim\Bundle\VersioningBundle\Doctrine;

use Pim\Bundle\VersioningBundle\Builder\VersionBuilder;
use Pim\Bundle\VersioningBundle\Manager\VersionContext;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Bundle\VersioningBundle\Model\VersionableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Base class for service to massively insert pending versions.
 * Useful for massive imports of products.
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractPendingMassPersister
{
    /** @var VersionBuilder */
    protected $versionBuilder;

    /** @var VersionManager */
    protected $versionManager;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var string */
    protected $versionClass;

    /** @var VersionContext  */
    protected $versionContext;

    /**
     * @param VersionBuilder      $versionBuilder
     * @param VersionManager      $versionManager
     * @param NormalizerInterface $normalizer
     * @param VersionContext      $versionContext
     * @param string              $versionClass
     */
    public function __construct(
        VersionBuilder $versionBuilder,
        VersionManager $versionManager,
        NormalizerInterface $normalizer,
        VersionContext $versionContext,
        $versionClass
    ) {
        $this->versionBuilder   = $versionBuilder;
        $this->versionManager   = $versionManager;
        $this->normalizer       = $normalizer;
        $this->versionClass     = $versionClass;
        $this->versionContext   = $versionContext;
    }

    /**
     * Create the pending versions for the versionable provided
     *
     * @param VersionableInterface[] $versionables
     */
    public function persistPendingVersions(array $versionables)
    {
        $author = $this->versionManager->getUsername();
        $context = $this->versionContext->getContextInfo();

        $pendingVersions = [];
        foreach ($versionables as $versionable) {
            $changeset = $this->normalizer->normalize($versionable, 'csv', ['versioning' => true]);

            $pendingVersions[] = $this->versionBuilder
                ->createPendingVersion($versionable, $author, $changeset, $context);
        }

        if (count($pendingVersions) > 0) {
            $this->batchInsertPendingVersions($pendingVersions);
        }
    }

    /**
     * Insert into pending versions
     *
     * @param array $pendingVersions
     */
    abstract protected function batchInsertPendingVersions(array $pendingVersions);
}
