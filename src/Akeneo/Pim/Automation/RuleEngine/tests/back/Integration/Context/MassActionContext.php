<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\RuleEngine\Integration\Context;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Behat\Behat\Context\Context;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class MassActionContext implements Context
{
    /** @var JobLauncher */
    protected $jobLauncherTest;

    /** @var JobLauncherInterface */
    protected $jobLauncher;

    /** @var JobInstanceRepository */
    private $jobInstanceRepository;

    /** @var UserProviderInterface */
    private $userProvider;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var EntityManagerClearerInterface */
    private $entityManagerClearer;

    public function __construct(
        JobLauncher $jobLauncherTest,
        JobLauncherInterface $jobLauncher,
        JobInstanceRepository $jobInstanceRepository,
        UserProviderInterface $userProvider,
        ProductRepositoryInterface $productRepository,
        ProductModelRepositoryInterface $productModelRepository,
        EntityManagerClearerInterface $entityManagerClearer
    ) {
        $this->jobLauncherTest = $jobLauncherTest;
        $this->jobLauncher = $jobLauncher;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->userProvider = $userProvider;
        $this->productRepository = $productRepository;
        $this->productModelRepository = $productModelRepository;
        $this->entityManagerClearer = $entityManagerClearer;
    }

    /**
     * @When /^I execute an edit attribute values bulk action to set the (?P<locale>\w+) (?P<scope>\w+) (?P<attributeCode>\w+) to "(?P<newValue>[^"]*)" for "(?P<productIdentifiers>[^"]*)"$/
     */
    public function executeAnEditAttributeValuesBulkActionFor(
        string $locale,
        string $scope,
        string $attributeCode,
        string $newValue,
        string $productIdentifiers
    ): void {
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier('edit_common_attributes');
        if (null === $jobInstance) {
            throw new \RuntimeException('The "edit_common_attributes" job instance is not found.');
        }

        $locale = 'unlocalized' === $locale ? null : $locale;
        $scope = 'unscoped' === $scope ? null : $scope;
        $productIds = $this->extractProductIds($productIdentifiers);
        $config = [
            'actions' => [
                [
                    'ui_locale' => 'en_US',
                    'attribute_locale' => $locale ?? 'en_US',
                    'attribute_channel' => $scope ?? 'tablet',
                    'normalized_values' => [
                        $attributeCode =>[
                            ['data' => $newValue, 'scope' => $scope, 'locale' =>  $locale],
                        ],
                    ],
                ],
            ],
            'filters' => [
                [
                    'field' => 'id',
                    'value' => $productIds,
                    'context' => ['scope' => $scope ?? 'tablet', 'locale' => $locale ?? 'en_US',],
                    'operator' => 'IN',
                ],
            ],
            'users_to_notify' => ['admin'],
            'realTimeVersioning' => true,
            'is_user_authenticated' => true,
        ];

        $user = $this->userProvider->loadUserByIdentifier('admin');
        $jobExecution = $this->jobLauncher->launch($jobInstance, $user, $config);
        $this->jobLauncherTest->launchConsumerOnce();
        $this->jobLauncherTest->waitCompleteJobExecution($jobExecution);
        $this->entityManagerClearer->clear();
    }

    private function extractProductIds(string $productIdentifiers): array
    {
        $productIds = [];
        foreach (explode(',', $productIdentifiers) as $productIdentifier) {
            $product = $this->productRepository->findOneByIdentifier(trim($productIdentifier));
            if (null !== $product) {
                $productIds[] = sprintf('product_%s', $product->getUuid()->toString());
                continue;
            }

            $productModel = $this->productModelRepository->findOneByIdentifier(trim($productIdentifier));
            if (null !== $productModel) {
                $productIds[] = sprintf('product_model_%d', $productModel->getId());
                continue;
            }

            throw new \InvalidArgumentException(
                sprintf('The "%s" product or product model does not exist.', $productIdentifier)
            );
        }

        return $productIds;
    }
}
