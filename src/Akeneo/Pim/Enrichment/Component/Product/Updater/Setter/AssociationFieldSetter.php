<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

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

    /** @var MissingAssociationAdder */
    private $missingAssociationAdder;

    /**
     * @param IdentifiableObjectRepositoryInterface $productRepository
     * @param IdentifiableObjectRepositoryInterface $productModelRepository
     * @param IdentifiableObjectRepositoryInterface $groupRepository
     * @param MissingAssociationAdder               $missingAssociationAdder
     * @param array                                 $supportedFields
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        IdentifiableObjectRepositoryInterface $groupRepository,
        MissingAssociationAdder $missingAssociationAdder,
        array $supportedFields
    ) {
        $this->productRepository = $productRepository;
        $this->productModelRepository = $productModelRepository;
        $this->groupRepository = $groupRepository;
        $this->supportedFields = $supportedFields;
        $this->missingAssociationAdder = $missingAssociationAdder;
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
        $this->clearAssociations($entity, $data);
        $this->addMissingAssociations($entity);
        $this->setProductsAndGroupsToAssociations($entity, $data);
    }

    /**
     * Clear only concerned associations (remove groups and products from existing associations)
     *
     * @param EntityWithAssociationsInterface $entity
     * @param array                           $data
     *
     * Expected data input format:
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
    protected function clearAssociations(EntityWithAssociationsInterface $entity, array $data = null)
    {
        if (null === $data) {
            return;
        }

        $entity->getAssociations()
            ->filter(function (AssociationInterface $association) use ($data) {
                return isset($data[$association->getAssociationType()->getCode()]);
            })
            ->forAll(function ($key, AssociationInterface $association) use ($data) {
                $currentData = $data[$association->getAssociationType()->getCode()];
                if (isset($currentData['products'])) {
                    foreach ($association->getProducts() as $productToRemove) {
                        $association->removeProduct($productToRemove);
                    }
                }
                if (isset($currentData['groups'])) {
                    foreach ($association->getGroups() as $groupToRemove) {
                        $association->removeGroup($groupToRemove);
                    }
                }
                if (isset($currentData['product_models'])) {
                    foreach ($association->getProductModels() as $productModelToRemove) {
                        $association->removeProductModel($productModelToRemove);
                    }
                }

                return true;
            });
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
     * Set products and groups to associations
     *
     * @param EntityWithAssociationsInterface $entity
     *
     * @throws InvalidPropertyException
     */
    protected function setProductsAndGroupsToAssociations(EntityWithAssociationsInterface $entity, $data)
    {
        foreach ($data as $typeCode => $items) {
            $typeCode = (string) $typeCode;
            $association = $entity->getAssociationForTypeCode($typeCode);
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
                $this->setAssociatedProducts($association, $items['products']);
            }
            if (isset($items['groups'])) {
                $this->setAssociatedGroups($association, $items['groups']);
            }
            if (isset($items['product_models'])) {
                $this->setAssociatedProductModels($association, $items['product_models']);
            }
        }
    }

    /**
     * @param AssociationInterface $association
     * @param array                $productModelsIdentifiers
     *
     * @throws InvalidPropertyException
     */
    protected function setAssociatedProductModels(AssociationInterface $association, $productModelsIdentifiers)
    {
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
            $association->addProductModel($associatedProductModel);
        }
    }

    /**
     * @param AssociationInterface $association
     * @param array                $productsIdentifiers
     *
     * @throws InvalidPropertyException
     */
    protected function setAssociatedProducts(AssociationInterface $association, $productsIdentifiers)
    {
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
            $association->addProduct($associatedProduct);
        }
    }

    /**
     * @param AssociationInterface $association
     * @param array                $groupsCodes
     *
     * @throws InvalidPropertyException
     */
    protected function setAssociatedGroups(AssociationInterface $association, $groupsCodes)
    {
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

    /**
     * Check if data are valid
     *
     * @param string $field
     * @param mixed  $data
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
            $assocTypeCode = (string) $assocTypeCode;
            $this->checkAssociationData($field, $data, $assocTypeCode, $items);
        }
    }

    /**
     * @param string $field
     * @param array  $data
     * @param string $assocTypeCode
     * @param mixed  $items
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
     * @param array  $data
     * @param array  $items
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
}
