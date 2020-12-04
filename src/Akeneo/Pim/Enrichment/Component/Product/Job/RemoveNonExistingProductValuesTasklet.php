<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Job;

use Akeneo\Pim\Enrichment\Component\Product\Storage\GetProductAndProductModelIdentifiersWithValuesIgnoringLocaleAndScope;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
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

    /** @var GetAttributes */
    private $getAttributes;

    /** @var GetProductAndProductModelIdentifiersWithValuesIgnoringLocaleAndScope */
    private $getProductAndProductModelIdentifiersWithValues;

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
        GetProductAndProductModelIdentifiersWithValuesIgnoringLocaleAndScope $getProductAndProductModelIdentifiersWithValues,
        GetAttributes $getAttributes,
        CursorableRepositoryInterface $productRepository,
        CursorableRepositoryInterface $productModelRepository,
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        EntityManagerClearerInterface $entityManagerClearer,
        int $batchSize
    ) {
        $this->getProductAndProductModelIdentifiersWithValues = $getProductAndProductModelIdentifiersWithValues;
        $this->getAttributes = $getAttributes;
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

        foreach ($batchIdentifiers as $identifiers) {
            $products = $this->productRepository->getItemsFromIdentifiers($identifiers);
            $this->productSaver->saveAll($products, ['force_save' => true]);

            $productModels = $this->productModelRepository->getItemsFromIdentifiers($identifiers);
            $this->productModelSaver->saveAll($productModels, ['force_save' => true]);

            $this->entityManagerClearer->clear();
        }
    }
}
