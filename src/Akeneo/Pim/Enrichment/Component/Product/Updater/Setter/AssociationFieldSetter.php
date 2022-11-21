<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidAssociationProductIdentifierException;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\TwoWayAssociationUpdaterInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Ramsey\Uuid\Uuid;

/**
 * Sets the association field
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationFieldSetter extends AbstractFieldSetter
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository,
        protected ProductModelRepositoryInterface $productModelRepository,
        protected GroupRepositoryInterface $groupRepository,
        private TwoWayAssociationUpdaterInterface $twoWayAssociationUpdater,
        private MissingAssociationAdder $missingAssociationAdder,
        private AssociationTypeRepositoryInterface $associationTypeRepository,
        array $supportedFields
    ) {
        $this->supportedFields = $supportedFields;
    }

    /**
     * {@inheritdoc}
     *
     * Expected data input format :
     * {
     *     "XSELL": {
     *         "groups": ["group1", "group2"],
     *         "products": ["AKN_TS1", "AKN_TSH2"],
     *         "product_models": ["MODEL_AKN_TS1", "MODEL_AKN_TSH2"],
     *         "product_uuids": ["662be899-6e69-43e2-88d0-0443e05ac5d7", "14ba0132-0202-4303-a997-f1b32469ef44"]
     *     },
     *     "UPSELL": {
     *         "groups": ["group3", "group4"],
     *         "products": ["AKN_TS3", "AKN_TSH4"],
     *         "product_models": ["MODEL_AKN_TS3 "MODEL_AKN_TSH4"],
     *         "product_uuids": ["662be899-6e69-43e2-88d0-0443e05ac5d7", "14ba0132-0202-4303-a997-f1b32469ef44"]
     *     },
     * }
     */
    public function setFieldData($entity, $field, $data, array $options = []): void
    {
        if (!$entity instanceof EntityWithAssociationsInterface) {
            throw InvalidObjectException::objectExpected($entity, EntityWithAssociationsInterface::class);
        }

        $this->checkData($field, $data);
        $this->missingAssociationAdder->addMissingAssociations($entity);
        $this->updateAssociations($entity, $data);
    }

    private function updateAssociations(EntityWithAssociationsInterface $entity, array $data): void
    {
        foreach ($data as $typeCode => $items) {
            $typeCode = (string)$typeCode;
            /** @var AssociationTypeInterface $associationType */
            $associationType = $this->associationTypeRepository->findOneByIdentifier($typeCode);
            if (null === $associationType || $associationType->isQuantified()) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'associations',
                    'association type code',
                    'The association type does not exist or is quantified',
                    static::class,
                    $typeCode
                );
            }
            if (isset($items['product_uuids'])) {
                $this->updateAssociatedProductUuids($entity, $associationType, $items['product_uuids']);
            }
            if (isset($items['products']) && !isset($items['product_uuids'])) {
                $this->updateAssociatedProducts($entity, $associationType, $items['products']);
            }
            if (isset($items['product_models'])) {
                $this->updateAssociatedProductModels($entity, $associationType, $items['product_models']);
            }
            if (isset($items['groups'])) {
                $this->updateAssociatedGroups($entity, $associationType, $items['groups']);
            }
        }
    }

    private function updateAssociatedProductUuids(
        EntityWithAssociationsInterface $owner,
        AssociationTypeInterface $associationType,
        array $productsUuids
    ): void {
        $productsUuids = array_unique($productsUuids);
        foreach ($owner->getAssociatedProducts($associationType->getCode()) as $associatedProduct) {
            $index = array_search($associatedProduct->getUuid(), $productsUuids);

            if (false === $index) {
                $this->removeAssociatedProduct($owner, $associatedProduct, $associationType);
            } else {
                unset($productsUuids[$index]);
            }
        }

        foreach ($productsUuids as $productUuid) {
            $associatedProduct = $this->productRepository->find($productUuid);
            if (null === $associatedProduct) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'associations',
                    'product uuid',
                    'The product does not exist',
                    static::class,
                    $productUuid
                );
            }
            $this->addAssociatedProduct($owner, $associatedProduct, $associationType);
        }
    }

    private function updateAssociatedProducts(
        EntityWithAssociationsInterface $owner,
        AssociationTypeInterface $associationType,
        array $productsIdentifiers
    ): void {
        $productsIdentifiers = array_unique($productsIdentifiers);
        foreach ($owner->getAssociatedProducts($associationType->getCode()) as $associatedProduct) {
            $index = array_search($associatedProduct->getIdentifier(), $productsIdentifiers);

            if (false === $index) {
                $this->removeAssociatedProduct($owner, $associatedProduct, $associationType);
            } else {
                unset($productsIdentifiers[$index]);
            }
        }

        foreach ($productsIdentifiers as $productIdentifier) {
            $associatedProduct = $this->productRepository->findOneByIdentifier($productIdentifier);
            if (null === $associatedProduct) {
                throw new InvalidAssociationProductIdentifierException(static::class, $productIdentifier);
            }
            $this->addAssociatedProduct($owner, $associatedProduct, $associationType);
        }
    }

    private function addAssociatedProduct(
        EntityWithAssociationsInterface $owner,
        ProductInterface $associatedProduct,
        AssociationTypeInterface $associationType
    ): void {
        $owner->addAssociatedProduct($associatedProduct, $associationType->getCode());

        if ($associationType->isTwoWay()) {
            $this->twoWayAssociationUpdater->createInversedAssociation(
                $owner,
                $associationType->getCode(),
                $associatedProduct
            );
        }
    }

    private function removeAssociatedProduct(
        EntityWithAssociationsInterface $owner,
        ProductInterface $associatedProduct,
        AssociationTypeInterface $associationType
    ): void {
        $owner->removeAssociatedProduct($associatedProduct, $associationType->getCode());

        if ($associationType->isTwoWay()) {
            $this->twoWayAssociationUpdater->removeInversedAssociation(
                $owner,
                $associationType->getCode(),
                $associatedProduct
            );
        }
    }

    private function updateAssociatedProductModels(
        EntityWithAssociationsInterface $owner,
        AssociationTypeInterface $associationType,
        array $productModelsIdentifiers
    ): void {
        $productModelsIdentifiers = array_unique($productModelsIdentifiers);

        foreach ($owner->getAssociatedProductModels($associationType->getCode()) as $associatedProductModel) {
            $index = array_search($associatedProductModel->getCode(), $productModelsIdentifiers);

            if (false === $index) {
                $this->removeAssociatedProductModel($owner, $associatedProductModel, $associationType);
            } else {
                unset($productModelsIdentifiers[$index]);
            }
        }

        foreach ($productModelsIdentifiers as $productModelIdentifier) {
            $associatedProductModel = $this->productModelRepository->findOneByIdentifier($productModelIdentifier);
            if (null === $associatedProductModel) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'associations',
                    'product model identifier',
                    'The product model does not exist',
                    static::class,
                    $productModelIdentifier
                );
            }
            $this->addAssociatedProductModel($owner, $associatedProductModel, $associationType);
        }
    }

    private function addAssociatedProductModel(
        EntityWithAssociationsInterface $owner,
        ProductModelInterface $associatedProductModel,
        AssociationTypeInterface $associationType
    ): void {
        $owner->addAssociatedProductModel($associatedProductModel, $associationType->getCode());

        if ($associationType->isTwoWay()) {
            $this->twoWayAssociationUpdater->createInversedAssociation(
                $owner,
                $associationType->getCode(),
                $associatedProductModel
            );
        }
    }

    private function removeAssociatedProductModel(
        EntityWithAssociationsInterface $owner,
        ProductModelInterface $associatedProductModel,
        AssociationTypeInterface $associationType
    ): void {
        $owner->removeAssociatedProductModel($associatedProductModel, $associationType->getCode());

        if ($associationType->isTwoWay()) {
            $this->twoWayAssociationUpdater->removeInversedAssociation(
                $owner,
                $associationType->getCode(),
                $associatedProductModel
            );
        }
    }

    private function updateAssociatedGroups(
        EntityWithAssociationsInterface $owner,
        AssociationTypeInterface $associationType,
        array $groupsCodes
    ): void {
        $groupsCodes = array_unique($groupsCodes);

        foreach ($owner->getAssociatedGroups($associationType->getCode()) as $associatedGroup) {
            $index = array_search($associatedGroup->getCode(), $groupsCodes);

            if (false === $index) {
                $owner->removeAssociatedGroup($associatedGroup, $associationType->getCode());
            } else {
                unset($groupsCodes[$index]);
            }
        }

        foreach ($groupsCodes as $groupCode) {
            $associatedGroup = $this->groupRepository->findOneByIdentifier($groupCode);
            if (null === $associatedGroup) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'associations',
                    'group code',
                    'The group does not exist',
                    static::class,
                    $groupCode
                );
            }
            $owner->addAssociatedGroup($associatedGroup, $associationType->getCode());
        }
    }

    protected function checkData(string $field, $data): void
    {
        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected(
                $field,
                static::class,
                $data
            );
        }

        foreach ($data as $assocTypeCode => $items) {
            $assocTypeCode = (string)$assocTypeCode;
            $this->checkAssociationData($field, $data, $assocTypeCode, $items);
        }
    }

    protected function checkAssociationData(string $field, array $data, string $assocTypeCode, $items): void
    {
        if (!is_array($items) || !is_string($assocTypeCode) ||
            (!isset($items['products']) && !isset($items['groups']) && !isset($items['product_models']) && !isset($items['product_uuids']))
        ) {
            throw InvalidPropertyTypeException::validArrayStructureExpected(
                $field,
                sprintf('association format is not valid for the association type "%s".', $assocTypeCode),
                static::class,
                $data
            );
        }

        if (isset($items['products']) && isset($items['product_uuids'])) {
            throw InvalidPropertyTypeException::validArrayStructureExpected(
                $field,
                \sprintf('association format is not valid for the association type "%s", only one of "products" or "product_uuids" is expected', $assocTypeCode),
                static::class,
                $data
            );
        }

        foreach ($items as $type => $itemData) {
            if (!is_array($itemData)) {
                $message = sprintf(
                    'Property "%s" in association "%s" expects an array as data, "%s" given.',
                    $type,
                    $assocTypeCode,
                    gettype($itemData)
                );

                throw new InvalidPropertyTypeException(
                    $type,
                    $itemData,
                    static::class,
                    $message,
                    InvalidPropertyTypeException::ARRAY_EXPECTED_CODE
                );
            }

            $this->checkAssociationItems($field, $assocTypeCode, $data, $itemData, $type);
        }
    }

    protected function checkAssociationItems(string $field, string $assocTypeCode, array $data, array $items, string $type): void
    {
        foreach ($items as $item) {
            if (!is_string($item)) {
                throw InvalidPropertyTypeException::validArrayStructureExpected(
                    $field,
                    sprintf('association format is not valid for the association type "%s".', $assocTypeCode),
                    static::class,
                    $data
                );
            }
            if ('product_uuids' === $type && !Uuid::isValid($item)) {
                throw InvalidPropertyTypeException::validArrayStructureExpected(
                    $field,
                    sprintf(
                        'association format is not valid for the association type "%s", "product_uuids" expects an array of valid uuids.',
                        $assocTypeCode
                    ),
                    static::class,
                    $data
                );
            }
        }
    }
}
