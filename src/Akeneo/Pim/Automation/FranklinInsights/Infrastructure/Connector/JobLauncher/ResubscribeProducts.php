<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobLauncher;

use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Service\ResubscribeProductsInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobInstanceNames;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ResubscribeProducts implements ResubscribeProductsInterface
{
    /** @var JobInstanceRepository */
    private $jobInstanceRepository;

    /** @var JobLauncherInterface */
    private $jobLauncher;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * @param JobInstanceRepository $jobInstanceRepository
     * @param JobLauncherInterface $jobLauncher
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        JobInstanceRepository $jobInstanceRepository,
        JobLauncherInterface $jobLauncher,
        TokenStorageInterface $tokenStorage
    ) {
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->jobLauncher = $jobLauncher;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param array $productIds
     */
    public function process(array $productIds): void
    {
        if (0 === empty($productIds)) {
            return;
        }

        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier(JobInstanceNames::RESUBSCRIBE_PRODUCTS);

        if (null === $jobInstance) {
            // TODO APAI-450: Should throw an exception.
            return;
        }

        $ids = array_map(
            function (ProductId $productId) {
                return sprintf('product_%d', $productId->toInt());
            },
            $productIds
        );

        $this->jobLauncher->launch(
            $jobInstance,
            $this->tokenStorage->getToken()->getUser(),
            [
                'filters' => [
                    [
                        'field' => 'id',
                        'operator' => 'IN',
                        'value' => $ids,
                    ],
                ],
            ]
        );
    }
}
