<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Service\ResubscribeProductsInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Query\SelectNonNullRequestedIdentifiersQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\SelectProductIdentifierValuesQueryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

/**
 * This tasklet identifies products that need resubscribing after updating the identifiers mapping.
 * It compares the new mapped identifier values of a product with the requested ones from the subscription,
 * and launches the resubcription job for impacted products.
 *
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class IdentifyProductToResubscribeTasklet implements TaskletInterface
{
    /** @var int */
    private const QUERY_BATCH_SIZE = 100;

    /** @var int */
    private const RESUBSCRIPTION_JOB_BATCH_SIZE = 10000;

    /** @var SelectProductIdentifierValuesQueryInterface */
    private $selectProductIdentifierValuesQuery;

    /** @var SelectNonNullRequestedIdentifiersQueryInterface */
    private $selectNonNullRequestedIdentifiersQuery;

    /** @var ResubscribeProductsInterface */
    private $resubscribeProducts;

    /** @var StepExecution */
    private $stepExecution;

    /** @var int[] */
    private $productIdsToResubscribe = [];

    /** @var int */
    private $searchAfter = 0;

    /**
     * @param SelectProductIdentifierValuesQueryInterface $selectProductIdentifierValuesQuery
     * @param SelectNonNullRequestedIdentifiersQueryInterface $selectNonNullRequestedIdentifiersQuery
     * @param ResubscribeProductsInterface $resubscribeProducts
     */
    public function __construct(
        SelectProductIdentifierValuesQueryInterface $selectProductIdentifierValuesQuery,
        SelectNonNullRequestedIdentifiersQueryInterface $selectNonNullRequestedIdentifiersQuery,
        ResubscribeProductsInterface $resubscribeProducts
    ) {
        $this->selectProductIdentifierValuesQuery = $selectProductIdentifierValuesQuery;
        $this->selectNonNullRequestedIdentifiersQuery = $selectNonNullRequestedIdentifiersQuery;
        $this->resubscribeProducts = $resubscribeProducts;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        $requestedIdentifiers = $this->getNextNonNullRequestedIdentifiers();
        while (!empty($requestedIdentifiers)) {
            $this->productIdsToResubscribe = array_merge(
                $this->productIdsToResubscribe,
                $this->getProductIdsWithUpdatedIdentifiers($requestedIdentifiers)
            );

            if (count($this->productIdsToResubscribe) >= self::RESUBSCRIPTION_JOB_BATCH_SIZE) {
                $this->launchResubscriptionJob();
            }
            $requestedIdentifiers = $this->getNextNonNullRequestedIdentifiers();
        }

        if (count($this->productIdsToResubscribe) > 0) {
            $this->launchResubscriptionJob();
        }
    }

    /**
     * Returns the next non null requested identifiers from subscriptions
     * which do have values for identifiers provided in the job parameters.
     *
     * @return array
     */
    private function getNextNonNullRequestedIdentifiers(): array
    {
        $updatedIdentifiers = $this->stepExecution->getJobParameters()->get('updated_identifiers');
        $requestedIdentifiers = $this->selectNonNullRequestedIdentifiersQuery->execute(
            $updatedIdentifiers,
            $this->searchAfter,
            self::QUERY_BATCH_SIZE
        );

        if (count($requestedIdentifiers) > 0) {
            end($requestedIdentifiers);
            $this->searchAfter = key($requestedIdentifiers);
        }

        return $requestedIdentifiers;
    }

    /**
     * Compares the new product mapped identifier values with the requested ones (from the subscription)
     * If a mapped value was updated or deleted (but not added), returns the matching productId.
     *
     * @param array $requestedIdentifierValues
     *
     * @return int[]
     */
    private function getProductIdsWithUpdatedIdentifiers(array $requestedIdentifierValues): array
    {
        $updatedProductIds = [];

        $newIdentifierValuesCollection = $this->selectProductIdentifierValuesQuery->execute(
            array_keys($requestedIdentifierValues)
        );

        foreach ($requestedIdentifierValues as $productId => $requestedIdentifierValuesForProduct) {
            $newIdentifierValues = $newIdentifierValuesCollection->get($productId);
            if (null === $newIdentifierValues) {
                continue;
            }

            foreach ($requestedIdentifierValuesForProduct as $franklinIdentifierCode => $formerIdentifierValue) {
                if (($newIdentifierValues->getValue($franklinIdentifierCode) ?? null) !== $formerIdentifierValue) {
                    $updatedProductIds[] = $productId;
                    break;
                }
            }
        }

        return $updatedProductIds;
    }

    /**
     * Launches the resubscription job for the current batch of product ids.
     */
    private function launchResubscriptionJob(): void
    {
        $this->resubscribeProducts->process($this->productIdsToResubscribe);
        $this->productIdsToResubscribe = [];
    }
}
