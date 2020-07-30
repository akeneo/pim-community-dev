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

namespace Akeneo\Pim\Enrichment\Component\Product\Job;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Storage\GetProductAndProductModelIdsWithValuesIgnoringLocaleAndScope;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Webmozart\Assert\Assert;

/**
 * Orchestrator to remove the non existing product values:
 *  - search the products/product models that have the values
 *  - remove the non existing values (the non existing values are removed during the hydration)
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class RemoveNonExistingProductValuesTasklet implements TaskletInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var GetProductAndProductModelIdsWithValuesIgnoringLocaleAndScope */
    private $getProductAndProductModelIdsWithValues;

    /** @var CursorableRepositoryInterface */
    private $productRepository;

    /** @var CursorableRepositoryInterface */
    private $productModelRepository;

    /** @var BulkSaverInterface */
    private $productSaver;

    /** @var BulkSaverInterface */
    private $productModelSaver;

    /** @var EntityManagerClearerInterface */
    private $entityManagerClearer;

    /** @var int */
    private $batchSize;

    public function __construct(
        GetProductAndProductModelIdsWithValuesIgnoringLocaleAndScope $getProductAndProductModelIdsWithValues,
        AttributeRepositoryInterface $attributeRepository,
        CursorableRepositoryInterface $productRepository,
        CursorableRepositoryInterface $productModelRepository,
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        EntityManagerClearerInterface $entityManagerClearer,
        int $batchSize
    ) {
        $this->getProductAndProductModelIdsWithValues = $getProductAndProductModelIdsWithValues;
        $this->attributeRepository = $attributeRepository;
        $this->productRepository = $productRepository;
        $this->productModelRepository = $productModelRepository;
        $this->productSaver = $productSaver;
        $this->productModelSaver = $productModelSaver;
        $this->entityManagerClearer = $entityManagerClearer;
        $this->batchSize = $batchSize;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     *
     * Loading the product filters the non existing values. We just need to save it again.
     */
    public function execute()
    {
        $filters = $this->stepExecution->getJobParameters()->get('filters');
        $this->checkFilters($filters);

        $filter = current($filters);
        $attributeCode = $filter['field'];
        $values = $filter['values'];

        $batchIdentifiers = $this->getProductAndProductModelIdsWithValues->forAttributeAndValues(
            $this->getAttribute($attributeCode),
            $values
        );

        foreach ($batchIdentifiers as $identifiers) {
            $products = $this->productRepository->getItemsFromIdentifiers($identifiers);
            $this->productSaver->saveAll($products);

            $productModels = $this->productModelRepository->getItemsFromIdentifiers($identifiers);
            $this->productModelSaver->saveAll($productModels);

            $this->entityManagerClearer->clear();
        }
    }

    private function getAttribute(string $attributeCode): Attribute
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
        if (null === $attribute) {
            throw new \InvalidArgumentException(sprintf('The "%s" attribute code was not found', $attributeCode));
        }

        return $attribute;
    }

    private function checkFilters(array $filters): void
    {
        Assert::count($filters, 1);
        $filter = current($filters);
        Assert::keyExists($filter, 'field');
        Assert::keyExists($filter, 'operator');
        Assert::keyExists($filter, 'values');
        Assert::same($filter['operator'], Operators::IN_LIST);
        Assert::isArray($filter['values']);
        Assert::allString($filter['values']);
    }
}
