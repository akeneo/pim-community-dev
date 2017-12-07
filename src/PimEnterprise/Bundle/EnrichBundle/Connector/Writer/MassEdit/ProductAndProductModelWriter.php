<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Connector\Writer\MassEdit;

use Akeneo\Component\StorageUtils\Cache\CacheClearerInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Model\EntityWithFamilyInterface;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Pim\Bundle\EnrichBundle\Connector\Writer\MassEdit\ProductAndProductModelWriter as BaseWriter;

/**
 * Product and product model writer
 *
 * @author Samir Boulil <samir.boulil@akeneo.com>
 */
class ProductAndProductModelWriter extends BaseWriter
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /**
     * @param VersionManager                $versionManager
     * @param BulkSaverInterface            $productSaver
     * @param BulkSaverInterface            $productModelSaver
     * @param CacheClearerInterface         $cacheClearer
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        VersionManager $versionManager,
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        CacheClearerInterface $cacheClearer,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        parent::__construct(
            $versionManager,
            $productSaver,
            $productModelSaver,
            $cacheClearer
        );

        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Returns true if user is owner of the entity with value or if the entity with value does not exist yet or if the
     * token does not exist
     *
     * @param EntityWithFamilyInterface $entityWithValue
     *
     * @return bool
     */
    protected function hasPermissions(EntityWithFamilyInterface $entityWithValue): bool
    {
        try {
            $hasRight = $this->hasRightsOverEntity($entityWithValue);
        } catch (AuthenticationCredentialsNotFoundException $e) {
            $hasRight = true;
        }

        return $hasRight;
    }

    /**
     * {@inheritdoc}
     */
    protected function incrementCount(EntityWithFamilyInterface $entityWithFamily): void
    {
        if ($this->hasPermissions($entityWithFamily)) {
            $this->stepExecution->incrementSummaryInfo('process');
        } else {
            $this->stepExecution->incrementSummaryInfo('proposal');
        }
    }

    /**
     * @param EntityWithFamilyInterface $entityWithValue
     *
     * @return bool
     */
    private function hasRightsOverEntity(EntityWithFamilyInterface $entityWithValue): bool
    {
        if (null === $entityWithValue->getId()) {
            return true;
        }

        return $this->authorizationChecker->isGranted(Attributes::OWN, $entityWithValue);
    }
}
