<?php

namespace Akeneo\Pim\Enrichment\Bundle\Product;

use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductModelsWithRemovedAttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductsAndProductModelsWithInheritedRemovedAttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductsWithRemovedAttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductIdentifiersWithRemovedAttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelIdentifiersWithRemovedAttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\ValuesRemover\CleanValuesOfRemovedAttributesInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CleanValuesOfRemovedAttributes implements CleanValuesOfRemovedAttributesInterface
{
    private const BATCH_SIZE = 100;

    /** @var CountProductsWithRemovedAttributeInterface */
    private $countProductsWithRemovedAttribute;

    /** @var CountProductModelsWithRemovedAttributeInterface */
    private $countProductModelsWithRemovedAttribute;

    /** @var CountProductsAndProductModelsWithInheritedRemovedAttributeInterface */
    private $countProductsAndProductModelsWithInheritedRemovedAttribute;

    /** @var GetProductIdentifiersWithRemovedAttributeInterface */
    private $getProductIdentifiersWithRemovedAttribute;

    /** @var GetProductModelIdentifiersWithRemovedAttributeInterface */
    private $getProductModelIdentifiersWithRemovedAttribute;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var BulkSaverInterface */
    private $productSaver;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var BulkSaverInterface */
    private $productModelSaver;

    /** @var ValidatorInterface */
    private $validator;

    /** @var GetAttributes */
    private $getAttributes;

    /** @var UnitOfWorkAndRepositoriesClearer */
    private $clearer;

    public function __construct(
        CountProductsWithRemovedAttributeInterface $countProductsWithRemovedAttribute,
        CountProductModelsWithRemovedAttributeInterface $countProductModelsWithRemovedAttribute,
        CountProductsAndProductModelsWithInheritedRemovedAttributeInterface $countProductsAndProductModelsWithInheritedRemovedAttribute,
        GetProductIdentifiersWithRemovedAttributeInterface $getProductIdentifiersWithRemovedAttribute,
        GetProductModelIdentifiersWithRemovedAttributeInterface $getProductModelIdentifiersWithRemovedAttribute,
        ProductRepositoryInterface $productRepository,
        BulkSaverInterface $productSaver,
        ProductModelRepositoryInterface $productModelRepository,
        BulkSaverInterface $productModelSaver,
        ValidatorInterface $validator,
        GetAttributes $getAttributes,
        UnitOfWorkAndRepositoriesClearer $clearer
    ) {
        $this->countProductsWithRemovedAttribute = $countProductsWithRemovedAttribute;
        $this->countProductModelsWithRemovedAttribute = $countProductModelsWithRemovedAttribute;
        $this->countProductsAndProductModelsWithInheritedRemovedAttribute = $countProductsAndProductModelsWithInheritedRemovedAttribute;
        $this->getProductIdentifiersWithRemovedAttribute = $getProductIdentifiersWithRemovedAttribute;
        $this->getProductModelIdentifiersWithRemovedAttribute = $getProductModelIdentifiersWithRemovedAttribute;
        $this->productRepository = $productRepository;
        $this->productSaver = $productSaver;
        $this->productModelRepository = $productModelRepository;
        $this->productModelSaver = $productModelSaver;
        $this->validator = $validator;
        $this->getAttributes = $getAttributes;
        $this->clearer = $clearer;
    }

    public function countProductsWithRemovedAttribute(array $attributesCodes): int
    {
        return $this->countProductsWithRemovedAttribute->count($attributesCodes);
    }

    public function cleanProductsWithRemovedAttribute(array $attributesCodes, ?callable $progress = null): void
    {
        foreach ($this->getProductIdentifiersWithRemovedAttribute->nextBatch($attributesCodes, self::BATCH_SIZE) as $identifiers) {
            $products = $this->productRepository->findBy(['identifier' => $identifiers]);
            $this->productSaver->saveAll($products, ['force_save' => true]);

            if (null !== $progress) {
                $progress(count($products));
            }

            $this->clearer->clear();
        }
    }

    public function countProductModelsWithRemovedAttribute(array $attributesCodes): int
    {
        return $this->countProductModelsWithRemovedAttribute->count($attributesCodes);
    }

    public function cleanProductModelsWithRemovedAttribute(array $attributesCodes, ?callable $progress = null): void
    {
        foreach ($this->getProductModelIdentifiersWithRemovedAttribute->nextBatch($attributesCodes, self::BATCH_SIZE) as $identifiers) {
            $productModels = $this->productModelRepository->findBy(['code' => $identifiers]);
            $this->productModelSaver->saveAll($productModels, ['force_save' => true]);

            if (null !== $progress) {
                $progress(count($productModels));
            }

            $this->clearer->clear();
        }
    }

    public function countProductsAndProductModelsWithInheritedRemovedAttribute(array $attributesCodes): int
    {
        return $this->countProductsAndProductModelsWithInheritedRemovedAttribute->count($attributesCodes);
    }

    public function validateRemovedAttributesCodes(array $attributesCodes): void
    {
        if (empty($attributesCodes)) {
            throw new \LogicException('The given attributes codes should not be empty.');
        }

        foreach ($attributesCodes as $attributeCode) {
            $this->validateAttributeCode($attributeCode);
        }
    }

    private function validateAttributeCode(string $attributeCode): void
    {
        $violations = $this->validator->validate($attributeCode, [
            new Assert\NotBlank(),
            new Assert\Length([
                'max' => 100,
            ]),
            new Assert\Regex('/^[a-zA-Z0-9_]+$/'),
            new Assert\Regex('/^(?!(id|iD|Id|ID|associationTypes|categories|categoryId|completeness|enabled|(?i)\bfamily\b|groups|associations|products|scope|treeId|values|category|parent|label|(.)*_(products|groups)|entity_type|attributes)$)/'),
            new Assert\Regex('/^[^\n]+$/D'),
        ]);

        if (count($violations) > 0) {
            throw new \InvalidArgumentException(sprintf('The attribute code "%s" is not valid.', $attributeCode));
        }

        $attribute = $this->getAttributes->forCode($attributeCode);

        if (null !== $attribute) {
            throw new \InvalidArgumentException(sprintf(
                'The attribute with the code "%s" still exists.',
                $attributeCode
            ));
        }
    }
}
