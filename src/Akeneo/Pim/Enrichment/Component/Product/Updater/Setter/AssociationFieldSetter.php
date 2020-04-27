<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Group;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;

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

    /** @var ManagerRegistry */
    private $registry;

    /**
     * @param IdentifiableObjectRepositoryInterface $productRepository
     * @param IdentifiableObjectRepositoryInterface $productModelRepository
     * @param IdentifiableObjectRepositoryInterface $groupRepository
     * @param MissingAssociationAdder $missingAssociationAdder
     * @param array $supportedFields
     */
    public function __construct(
//        IdentifiableObjectRepositoryInterface $productRepository,
//        IdentifiableObjectRepositoryInterface $productModelRepository,
//        IdentifiableObjectRepositoryInterface $groupRepository,
        ManagerRegistry $registry,
        MissingAssociationAdder $missingAssociationAdder,
        array $supportedFields
    ) {
//        $this->productRepository = $productRepository;
//        $this->productModelRepository = $productModelRepository;
//        $this->groupRepository = $groupRepository;
        $this->registry = $registry;
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

    protected function updateAssociations(EntityWithAssociationsInterface $entity, $data)
    {
        foreach ($data as $typeCode => $items) {
            $typeCode = (string)$typeCode;
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
                $this->updateAssociatedProducts($association, $items['products']);
            }
            if (isset($items['groups'])) {
                $this->updateAssociatedGroups($association, $items['groups']);
            }
            if (isset($items['product_models'])) {
                $this->updateAssociatedProductModels($association, $items['product_models']);
            }
        }
    }

    protected function updateAssociatedProducts(AssociationInterface $association, $productsIdentifiers)
    {
        $em = $this->registry->getManager();
        $owner = $association->getOwner();
        $associationType = $association->getAssociationType();

        // todo explain why
        $identifiers = array_flip($productsIdentifiers);

        /** @var ProductInterface $product */
        foreach ($association->getProducts() as $associatedProduct) {
            if (!isset($identifiers[$associatedProduct->getIdentifier()])) {
                $this->removeAssociatedProduct($association, $associatedProduct);
            } else {
                unset($identifiers[$associatedProduct->getIdentifier()]);
            }
        }

        // Here, we only have identifiers that were not present. We add them.
        foreach (array_keys($identifiers) as $productIdentifier) {
            $associatedProduct = $em->getRepository(Product::class)->findOneByIdentifier($productIdentifier);
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

    protected function addAssociatedProduct(AssociationInterface $association, $associatedProduct)
    {
        $association->addProduct($associatedProduct);

        if ($association->getAssociationType()->isTwoWay()) {
            $this->inverseAssociation($association, $associatedProduct);
        }
    }

    protected function removeAssociatedProduct(AssociationInterface $association, $associatedProduct)
    {
        $association->removeProduct($associatedProduct);

        if ($association->getAssociationType()->isTwoWay()) {
            $this->removeInversedAssociation($association, $associatedProduct);
        }
    }

    protected function updateAssociatedGroups(AssociationInterface $association, $groupsCodes)
    {
        $em = $this->registry->getManager();
        $owner = $association->getOwner();
        $associationType = $association->getAssociationType();

        // todo explain why
        $identifiers = array_flip($groupsCodes);

        /** @var GroupInterface $associatedGroup */
        foreach ($association->getGroups() as $associatedGroup) {
            if (!isset($identifiers[$associatedGroup->getCode()])) {
                $association->removeGroup($associatedGroup);
            } else {
                unset($identifiers[$associatedGroup->getCode()]);
            }
        }

        foreach ($groupsCodes as $groupCode) {
            $associatedGroup = $em->getRepository(Group::class)->findOneByIdentifier($groupCode);
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
     * @param AssociationInterface $association
     * @param $productModelsIdentifiers
     */
    protected function updateAssociatedProductModels(AssociationInterface $association, $productModelsIdentifiers)
    {
        $em = $this->registry->getManager();
        $owner = $association->getOwner();
        $associationType = $association->getAssociationType();

        $identifiers = array_flip($productModelsIdentifiers);

        /** @var ProductModelInterface $associatedProductModel */
        foreach ($association->getProductModels() as $associatedProductModel) {
            if (!isset($identifiers[$associatedProductModel->getCode()])) {
                $this->removeAssociatedProductModel($association, $associatedProductModel);
            } else {
                unset($identifiers[$associatedProductModel->getCode()]);
            }
        }

        foreach ($productModelsIdentifiers as $productModelIdentifier) {
            $associatedProductModel = $em->getRepository(ProductModel::class)->findOneByIdentifier(
                $productModelIdentifier
            );
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

    /**
     * @param AssociationInterface $association
     * @param ProductModelInterface $associatedProductModel
     */
    protected function addAssociatedProductModel(AssociationInterface $association, $associatedProductModel)
    {
        $association->addProductModel($associatedProductModel);

        if ($association->getAssociationType()->isTwoWay()) {
            $this->inverseAssociation($association, $associatedProductModel);
        }
    }

    protected function removeAssociatedProductModel(AssociationInterface $association, $associatedProductModel)
    {
        $association->removeProductModel($associatedProductModel);

        if ($association->getAssociationType()->isTwoWay()) {
            $this->removeInversedAssociation($association, $associatedProductModel);
        }
    }

    protected function removeInversedAssociation($association, EntityWithAssociationsInterface $associatedEntity): void
    {
        $em = $this->registry->getManager();

        $owner = $association->getOwner();
        $associationType = $association->getAssociationType();

        $inversedAssociation = $associatedEntity->getAssociationForType($associationType);
        if (null !== $inversedAssociation) {
            if ($owner instanceof ProductInterface) {
                $inversedAssociation->removeProduct($owner);
            } elseif ($owner instanceof ProductModelInterface) {
                $inversedAssociation->removeProductModel($owner);
            } else {
                throw new \LogicException('not implemented');
            }

            $em->persist($inversedAssociation);
        }
    }

    /**
     * @param AssociationInterface $association
     * @param EntityWithAssociationsInterface $associatedEntity
     */
    protected function inverseAssociation(
        AssociationInterface $association,
        EntityWithAssociationsInterface $associatedEntity
    ): void {
        $em = $this->registry->getManager();

        $associationType = $association->getAssociationType();
        $owner = $association->getOwner();

        /** @var AssociationInterface $inversedAssociation */
        $inversedAssociation = $associatedEntity->getAssociationForType($associationType);
        if (null === $inversedAssociation) {
            $this->addMissingAssociations($associatedEntity);
            $inversedAssociation = $associatedEntity->getAssociationForType($associationType);
        }

        if ($owner instanceof ProductInterface) {
            $inversedAssociation->addProduct($owner);
        } elseif ($owner instanceof ProductModelInterface) {
            $inversedAssociation->addProductModel($owner);
        } else {
            throw new \LogicException('not implemented');
        }

        $em->persist($inversedAssociation);
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

//    /**
//     * Set products and groups to associations
//     *
//     * @param EntityWithAssociationsInterface $entity
//     *
//     * @throws InvalidPropertyException
//     */
//    protected function setProductsAndGroupsToAssociations(EntityWithAssociationsInterface $entity, $data)
//    {
//        foreach ($data as $typeCode => $items) {
//            $typeCode = (string)$typeCode;
//            $association = $entity->getAssociationForTypeCode($typeCode);
//            if (null === $association) {
//                throw InvalidPropertyException::validEntityCodeExpected(
//                    'associations',
//                    'association type code',
//                    'The association type does not exist',
//                    static::class,
//                    $typeCode
//                );
//            }
//            if (isset($items['products'])) {
//                $this->setAssociatedProducts($association, $items['products']);
//            }
//            if (isset($items['groups'])) {
//                $this->setAssociatedGroups($association, $items['groups']);
//            }
//            if (isset($items['product_models'])) {
//                $this->setAssociatedProductModels($association, $items['product_models']);
//            }
//        }
//    }
//
//    /**
//     * @param AssociationInterface $association
//     * @param array $productModelsIdentifiers
//     *
//     * @throws InvalidPropertyException
//     */
//    protected function setAssociatedProductModels(AssociationInterface $association, $productModelsIdentifiers)
//    {
//        foreach ($productModelsIdentifiers as $productModelIdentifier) {
//            $associatedProductModel = $this->productModelRepository->findOneByIdentifier($productModelIdentifier);
//            if (null === $associatedProductModel) {
//                throw InvalidPropertyException::validEntityCodeExpected(
//                    'associations',
//                    'Product model identifier',
//                    'The product model does not exist',
//                    static::class,
//                    $productModelIdentifier
//                );
//            }
//            $association->addProductModel($associatedProductModel);
//        }
//    }
//
//    /**
//     * @param AssociationInterface $association
//     * @param array $productsIdentifiers
//     *
//     * @throws InvalidPropertyException
//     */
//    protected function setAssociatedProducts(AssociationInterface $association, $productsIdentifiers)
//    {
//        foreach ($productsIdentifiers as $productIdentifier) {
//            $associatedProduct = $this->productRepository->findOneByIdentifier($productIdentifier);
//            if (null === $associatedProduct) {
//                throw InvalidPropertyException::validEntityCodeExpected(
//                    'associations',
//                    'product identifier',
//                    'The product does not exist',
//                    static::class,
//                    $productIdentifier
//                );
//            }
//            $association->addProduct($associatedProduct);
//        }
//    }
//
//    /**
//     * @param AssociationInterface $association
//     * @param array $groupsCodes
//     *
//     * @throws InvalidPropertyException
//     */
//    protected function setAssociatedGroups(AssociationInterface $association, $groupsCodes)
//    {
//        foreach ($groupsCodes as $groupCode) {
//            $associatedGroup = $this->groupRepository->findOneByIdentifier($groupCode);
//            if (null === $associatedGroup) {
//                throw InvalidPropertyException::validEntityCodeExpected(
//                    'associations',
//                    'group code',
//                    'The group does not exist',
//                    static::class,
//                    $groupCode
//                );
//            }
//            $association->addGroup($associatedGroup);
//        }
//    }

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
}
