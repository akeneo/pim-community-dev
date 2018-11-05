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

use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Bundle\EnrichBundle\Connector\Writer\MassEdit\ProductAndProductModelWriter as BaseWriter;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Model\EntityWithFamilyInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

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
     * @param BulkSaverInterface                         $productSaver
     * @param BulkSaverInterface                         $productModelSaver
     * @param VersionManager                             $versionManager
     * @param EntityManagerClearerInterface              $cacheClearer
     * @param AuthorizationCheckerInterface              $authorizationChecker
     * @param TokenStorageInterface|null                 $tokenStorage
     * @param JobLauncherInterface|null                  $jobLauncher
     * @param IdentifiableObjectRepositoryInterface|null $jobInstanceRepository
     * @param string|null                                $jobName
     *
     * @todo @merge On master : remove $cacheClearer. It is not used anymore. The cache is now cleared in a dedicated subscriber.
     */
    public function __construct(
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        VersionManager $versionManager,
        EntityManagerClearerInterface $cacheClearer,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage = null, //TODO remove following nullables before merge on 3.x
        JobLauncherInterface $jobLauncher = null,
        IdentifiableObjectRepositoryInterface $jobInstanceRepository = null,
        string $jobName = null
    ) {
        parent::__construct(
            $versionManager,
            $productSaver,
            $productModelSaver,
            $cacheClearer,
            $tokenStorage,
            $jobLauncher,
            $jobInstanceRepository,
            $jobName
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
