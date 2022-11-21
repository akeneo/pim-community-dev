<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Job;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Storage\GetProductAndProductModelIdentifiersWithValuesIgnoringLocaleAndScope;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
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
    private ?StepExecution $stepExecution = null;

    public function __construct(
        private GetProductAndProductModelIdentifiersWithValuesIgnoringLocaleAndScope $getProductAndProductModelIdentifiersWithValues,
        private GetAttributes $getAttributes,
        private ProductRepositoryInterface $productRepository,
        private ProductModelRepositoryInterface $productModelRepository,
        private BulkSaverInterface $productSaver,
        private BulkSaverInterface $productModelSaver,
        private EntityManagerClearerInterface $entityManagerClearer,
        private int $batchSize
    ) {
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
     *
     * Loading the product filters the non existing values. We just need to save it again.
     */
    public function execute(): void
    {
        $attributeCode = $this->stepExecution->getJobParameters()->get('attribute_code');
        Assert::string($attributeCode);
        $values = $this->stepExecution->getJobParameters()->get('attribute_options');
        Assert::isArray($values);

        $attribute = $this->getAttributes->forCode($attributeCode);
        if (null === $attribute) {
            throw new \InvalidArgumentException(sprintf('The "%s" attribute code was not found', $attributeCode));
        }

        $batchIdentifiers = $this->getProductAndProductModelIdentifiersWithValues->forAttributeAndValues(
            $attribute->code(),
            $attribute->backendType(),
            $values
        );

        foreach ($batchIdentifiers as $identifierResults) {
            foreach (\array_chunk($identifierResults->getProductUuids(), $this->batchSize) as $productUuids) {
                $products = $this->productRepository->getItemsFromUuids($productUuids);
                $this->productSaver->saveAll($products, ['force_save' => true]);
            }

            foreach (\array_chunk($identifierResults->getProductModelIdentifiers(), $this->batchSize) as $productModelCodes) {
                $productModels = $this->productModelRepository->getItemsFromIdentifiers($productModelCodes);
                $this->productModelSaver->saveAll($productModels, ['force_save' => true]);
            }

            $this->entityManagerClearer->clear();
        }
    }
}
