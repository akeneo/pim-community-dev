<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\TwoWayAssociationUpdaterInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\Collection;
use Webmozart\Assert\Assert;

/**
 * Sets the association field
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationFieldSetter extends AbstractFieldSetter
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $productRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $productModelRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $groupRepository;

    /** @var TwoWayAssociationUpdaterInterface */
    private $twoWayAssociationUpdater;

    /** @var MissingAssociationAdder */
    private $missingAssociationAdder;

    /**
     * @param IdentifiableObjectRepositoryInterface $productRepository
     * @param IdentifiableObjectRepositoryInterface $productModelRepository
     * @param IdentifiableObjectRepositoryInterface $groupRepository
     * @param TwoWayAssociationUpdaterInterface $twoWayAssociationUpdater
     * @param MissingAssociationAdder $missingAssociationAdder
     * @param array $supportedFields
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        IdentifiableObjectRepositoryInterface $groupRepository,
        TwoWayAssociationUpdaterInterface $twoWayAssociationUpdater,
        MissingAssociationAdder $missingAssociationAdder,
        array $supportedFields
    ) {
        $this->productRepository = $productRepository;
        $this->productModelRepository = $productModelRepository;
        $this->groupRepository = $groupRepository;
        $this->twoWayAssociationUpdater = $twoWayAssociationUpdater;
        $this->missingAssociationAdder = $missingAssociationAdder;
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
     *         "product_models": ["MODEL_AKN_TS1", "MODEL_AKN_TSH2"]
     *     },
     *     "UPSELL": {
     *         "groups": ["group3", "group4"],
     *         "products": ["AKN_TS3", "AKN_TSH4"],
     *         "product_models": ["MODEL_AKN_TS3 "MODEL_AKN_TSH4"]
     *     },
     * }
     */
    public function setFieldData($entity, $field, $data, array $options = [])
    {
        if (!$entity instanceof EntityWithValuesInterface) {
            throw InvalidObjectException::objectExpected($entity, EntityWithValuesInterface::class);
        }

        $this->checkData($field, $data);
        $this->addMissingAssociations($entity);
        $this->updateAssociations($entity, $data);
    }

    private function updateAssociations(EntityWithAssociationsInterface $entity, array $data): void
    {
        $associations = $entity->getAssociations();
        foreach ($data as $typeCode => $items) {
            $typeCode = (string)$typeCode;
            $association = $this->getAssociationForTypeCode($associations, $typeCode);
            if (null === $association) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'associations',
                    'association type code',
                    'The association type does not exist',
                    static::class,
                    $typeCode
                );
            }
            if (isset($items['products'])) {
                $this->updateAssociatedProducts($association, $items['products']);
            }
            if (isset($items['product_models'])) {
                $this->updateAssociatedProductModels($association, $items['product_models']);
            }
            if (isset($items['groups'])) {
                $this->updateAssociatedGroups($association, $items['groups']);
            }
        }
        $entity->setAssociations($associations);
    }

    private function updateAssociatedProducts(AssociationInterface $association, array $productsIdentifiers): void
    {
        $productsIdentifiers = array_unique($productsIdentifiers);

        foreach ($association->getProducts() as $associatedProduct) {
            $index = array_search($associatedProduct->getIdentifier(), $productsIdentifiers);

            if (false === $index) {
                $this->removeAssociatedProduct($association, $associatedProduct);
            } else {
                unset($productsIdentifiers[$index]);
            }
        }

        foreach ($productsIdentifiers as $productIdentifier) {
            $associatedProduct = $this->productRepository->findOneByIdentifier($productIdentifier);
            if (null === $associatedProduct) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'associations',
                    'product identifier',
                    'The product does not exist',
                    static::class,
                    $productIdentifier
                );
            }
            $this->addAssociatedProduct($association, $associatedProduct);
        }
    }

    private function addAssociatedProduct(AssociationInterface $association, ProductInterface $associatedProduct): void
    {
        $association->addProduct($associatedProduct);

        if ($association->getAssociationType()->isTwoWay()) {
            $this->createInversedAssociation($association, $associatedProduct);
        }
    }

    private function removeAssociatedProduct(
        AssociationInterface $association,
        ProductInterface $associatedProduct
    ): void {
        $association->removeProduct($associatedProduct);

        if ($association->getAssociationType()->isTwoWay()) {
            $this->removeInversedAssociation($association, $associatedProduct);
        }
    }

    private function updateAssociatedProductModels(
        AssociationInterface $association,
        array $productModelsIdentifiers
    ): void {
        $productModelsIdentifiers = array_unique($productModelsIdentifiers);

        foreach ($association->getProductModels() as $associatedProductModel) {
            $index = array_search($associatedProductModel->getCode(), $productModelsIdentifiers);

            if (false === $index) {
                $this->removeAssociatedProductModel($association, $associatedProductModel);
            } else {
                unset($productModelsIdentifiers[$index]);
            }
        }

        foreach ($productModelsIdentifiers as $productModelIdentifier) {
            $associatedProductModel = $this->productModelRepository->findOneByIdentifier($productModelIdentifier);
            if (null === $associatedProductModel) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'associations',
                    'Product model identifier',
                    'The product model does not exist',
                    static::class,
                    $productModelIdentifier
                );
            }
            $this->addAssociatedProductModel($association, $associatedProductModel);
        }
    }

    private function addAssociatedProductModel(
        AssociationInterface $association,
        ProductModelInterface $associatedProductModel
    ): void {
        $association->addProductModel($associatedProductModel);

        if ($association->getAssociationType()->isTwoWay()) {
            $this->createInversedAssociation($association, $associatedProductModel);
        }
    }

    private function removeAssociatedProductModel(
        AssociationInterface $association,
        ProductModelInterface $associatedProductModel
    ): void {
        $association->removeProductModel($associatedProductModel);

        if ($association->getAssociationType()->isTwoWay()) {
            $this->removeInversedAssociation($association, $associatedProductModel);
        }
    }

    private function updateAssociatedGroups(AssociationInterface $association, array $groupsCodes): void
    {
        $groupsCodes = array_unique($groupsCodes);

        foreach ($association->getGroups() as $associatedGroup) {
            $index = array_search($associatedGroup->getCode(), $groupsCodes);

            if (false === $index) {
                $association->removeGroup($associatedGroup);
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
            $association->addGroup($associatedGroup);
        }
    }

    private function createInversedAssociation(
        AssociationInterface $association,
        EntityWithAssociationsInterface $associatedEntity
    ): void {
        $this->twoWayAssociationUpdater->createInversedAssociation($association, $associatedEntity);
    }

    private function removeInversedAssociation(
        AssociationInterface $association,
        EntityWithAssociationsInterface $associatedEntity
    ): void {
        $this->twoWayAssociationUpdater->removeInversedAssociation($association, $associatedEntity);
    }

    /**
     * Add missing associations (if association type has been added after the last processing)
     *
     * @param EntityWithAssociationsInterface $entity
     */
    protected function addMissingAssociations(EntityWithAssociationsInterface $entity)
    {
        $this->missingAssociationAdder->addMissingAssociations($entity);
    }

    /**
     * Check if data are valid
     *
     * @param string $field
     * @param mixed $data
     *
     * @throws InvalidPropertyTypeException
     */
    protected function checkData($field, $data)
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

    /**
     * @param string $field
     * @param array $data
     * @param string $assocTypeCode
     * @param mixed $items
     *
     * @throws InvalidPropertyTypeException
     */
    protected function checkAssociationData($field, array $data, $assocTypeCode, $items)
    {
        if (!is_array($items) || !is_string($assocTypeCode) ||
            (!isset($items['products']) && !isset($items['groups']) && !isset($items['product_models']))
        ) {
            throw InvalidPropertyTypeException::validArrayStructureExpected(
                $field,
                sprintf('association format is not valid for the association type "%s".', $assocTypeCode),
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

            $this->checkAssociationItems($field, $assocTypeCode, $data, $itemData);
        }
    }

    /**
     * @param string $field
     * @param string $assocTypeCode
     * @param array $data
     * @param array $items
     *
     * @throws InvalidPropertyTypeException
     */
    protected function checkAssociationItems($field, $assocTypeCode, array $data, array $items)
    {
        foreach ($items as $code) {
            if (!is_string($code)) {
                throw InvalidPropertyTypeException::validArrayStructureExpected(
                    $field,
                    sprintf('association format is not valid for the association type "%s".', $assocTypeCode),
                    static::class,
                    $data
                );
            }
        }
    }

    private function getAssociationForTypeCode(Collection $associations, string $typeCode): ?AssociationInterface
    {
        foreach ($associations as $association) {
            Assert::isInstanceOf($association, AssociationInterface::class);
            if ($typeCode === $association->getAssociationType()->getCode()) {
                return $association;
            }
        }

        return null;
    }
}
