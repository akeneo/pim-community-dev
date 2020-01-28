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

namespace Akeneo\Pim\Permission\Bundle\MassEdit\Writer;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\Database\MassEdit\ProductAndProductModelWriter as BaseWriter;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

/**
 * Product and product model writer.
 *
 * @author Samir Boulil <samir.boulil@akeneo.com>
 */
class ProductAndProductModelWriter extends BaseWriter
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /**
     * @param BulkSaverInterface                    $productSaver
     * @param BulkSaverInterface                    $productModelSaver
     * @param VersionManager                        $versionManager
     * @param AuthorizationCheckerInterface         $authorizationChecker
     */
    public function __construct(
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        VersionManager $versionManager,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        parent::__construct(
            $versionManager,
            $productSaver,
            $productModelSaver,
        );

        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Returns true if user is owner of the entity with value or if the entity with value does not exist yet or if the
     * token does not exist.
     *
     * @param EntityWithFamilyInterface $entityWithValue
     *
     * @return bool
     */
    protected function hasPermissions(EntityWithFamilyInterface $entityWithValue): bool
    {
        if (null === $entityWithValue->getId()) {
            return true;
        }

        try {
            $hasRight = $this->authorizationChecker->isGranted(Attributes::OWN, $entityWithValue);
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
        if ($this->hasPermissions($entityWithFamily) || $entityWithFamily instanceof ProductModelInterface) {
            $this->stepExecution->incrementSummaryInfo('process');
        } elseif ($entityWithFamily) {
            $this->stepExecution->incrementSummaryInfo('proposal');
        }
    }
}
