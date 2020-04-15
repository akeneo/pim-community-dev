<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder;
use Akeneo\Pim\Enrichment\Component\Product\Association\MissingQuantifiedAssociationAdder;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedProductAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedProductProductAssociation;
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
class QuantifiedAssociationFieldSetter extends AbstractFieldSetter
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $productRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $productModelRepository;

    /** @var MissingAssociationAdder */
    private $missingAssociationAdder;

    /**
     * @param IdentifiableObjectRepositoryInterface $productRepository
     * @param IdentifiableObjectRepositoryInterface $productModelRepository
     * @param MissingAssociationAdder               $missingAssociationAdder
     * @param array                                 $supportedFields
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        MissingQuantifiedAssociationAdder $missingAssociationAdder,
        array $supportedFields
    ) {
        $this->productRepository = $productRepository;
        $this->productModelRepository = $productModelRepository;
        $this->supportedFields = $supportedFields;
        $this->missingAssociationAdder = $missingAssociationAdder;
    }

    /**
     * {@inheritdoc}
     *
     * Expected data input format :
     * {
     *     "XSELL": {
     *         "products": [{"identifier": "AKN_TS1", "quantity": 12}],
     *         "products": [{"identifier": "AKN_TS1", "quantity": 12}],
     *     },
     *     "UPSELL": {
     *         "products": [{"identifier": "AKN_TS1", "quantity": 12}],
     *         "products": [{"identifier": "AKN_TS1", "quantity": 12}],
     *     },
     * }
     */
    public function setFieldData($entity, $field, $data, array $options = [])
    {
        if (!$entity instanceof EntityWithValuesInterface) {
            throw InvalidObjectException::objectExpected($entity, EntityWithValuesInterface::class);
        }

        // TODO: Remove unused association types
        /**
         * @var string $associationTypeCode
         * @var array $quantifiedAssociations
         */
        foreach ($data as $associationTypeCode => $quantifiedAssociations) {
            $updatedProductIdentifiers = array_map(function($quantifiedAssociation) {
                return $quantifiedAssociation['identifier'];
            }, $quantifiedAssociations['products']);

            /* @var QuantifiedProductAssociation $productAssociation*/
            $productAssociation = $entity->getQuantifiedAssociations()
                ->filter(function (QuantifiedProductAssociation $association) use ($associationTypeCode) {
                    return $association->getAssociationType()->getCode() === $associationTypeCode;
                })->toArray()[0];
            $currentProductIdentifiers = $productAssociation->getQuantifiedProducts()->map(
                function (QuantifiedProductProductAssociation $productProductAssociation) {
                    return $productProductAssociation->product->getReference();
                }
            )->toArray();

//            $currentProductIdentifiers = array_reduce($productAssociation, function ($carry, QuantifiedProductAssociation $item) {
//                $productIdentifiers = $item->getQuantifiedProducts()->map(function(QuantifiedProductProductAssociation $productProductAssociation) {
//                    return $productProductAssociation->product->getReference();
//                })->toArray();
//
//                return array_merge($carry, $productIdentifiers);
//            }, []);

//        var_dump($updatedProductIdentifiers);
//        var_dump($currentProductIdentifiers);
            // Chopper les codes de tous les produits target actuellement dans l'entité
            $modifiedProducts = array_intersect($currentProductIdentifiers, $updatedProductIdentifiers);
            $delete = array_diff($currentProductIdentifiers, $updatedProductIdentifiers);
            $add = array_diff($updatedProductIdentifiers, $currentProductIdentifiers);

//            var_dump($modifiedProducts);
//            var_dump($delete);
//            var_dump($add);

            foreach ($delete as $productIdentifierToDelete) {
                $productAssociation->removeProductForIdentifier($productIdentifierToDelete);
            }
            foreach ($modifiedProducts as $productTomodify) {
                $quantifiedAssociationForProduct = array_filter($quantifiedAssociations, function ($quantifiedAssociation) use ($productTomodify) {
                    var_dump($quantifiedAssociations);
                    return $quantifiedAssociation['identifier'] === $productTomodify;
                });
                $productAssociation->updateProductForIdentifier($productTomodify, $quantifiedAssociationForProduct['quantity']);
            }

            foreach ($add as $productToAdd) {
                $quantifiedAssociationForProduct = array_filter($quantifiedAssociations, function ($quantifiedAssociation) use ($productToAdd) {
                    return $quantifiedAssociation['identifier'] === $productToAdd;
                });
                $product = $this->productRepository->findOneByIdentifier($productToAdd);

                $quantifiedProductProduct = new QuantifiedProductProductAssociation();
                $quantifiedProductProduct->product = $product;
                $quantifiedProductProduct->quantity = $quantifiedAssociationForProduct['quantity'];
                $quantifiedProductProduct->association = $productAssociation;

                $productAssociation->addProductForIdentifier($quantifiedProductProduct);
            }
        }

        // Regarder tous les codes de produits de ceux qu'ont doit mettre à jour => tableau de ceux à supprimer + tableaux de ceux à updater et ceux à ajouter

        // Mettre à jour les entités avec les 3 boulots


        $this->checkData($field, $data);
//        $this->clearAssociations($entity, $data);
//        $this->addMissingAssociations($entity);
//        $this->setProductsAndGroupsToAssociations($entity, $data);
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
    protected function clearAssociations($entity, array $data = null)
    {
        if (null === $data) {
            return;
        }

        $entity->getQuantifiedAssociations()
            ->filter(function (QuantifiedProductAssociation $association) use ($data) {
                return isset($data[$association->getAssociationType()->getCode()]);
            })
            ->forAll(function ($key, QuantifiedProductAssociation $association) use ($data) {
                $currentData = $data[$association->getAssociationType()->getCode()];
                if (isset($currentData['products'])) {
                    foreach ($association->getQuantifiedProducts() as $productToRemove) {
                        $association->removeQuantifiedProduct($productToRemove);
                    }
                }
                // if (isset($currentData['product_models'])) {
                //     foreach ($association->getProductModels() as $productModelToRemove) {
                //         $association->removeProductModel($productModelToRemove);
                //     }
                // }

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
    protected function setProductsAndGroupsToAssociations($entity, $data)
    {
        foreach ($data as $typeCode => $items) {
            $typeCode = (string) $typeCode;
            $association = $entity->getQuantifiedAssociationForTypeCode($typeCode);
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
    protected function setAssociatedProducts(QuantifiedAssociationInterface $quantifiedAssociation, $productsIdentifiers)
    {
        foreach ($productsIdentifiers as $quantifiedProductToAssociate) {
            $productToAssociate = $this->productRepository->findOneByIdentifier($quantifiedProductToAssociate['identifier']);
            if (null === $productToAssociate) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'associations',
                    'product identifier',
                    'The product does not exist',
                    static::class,
                    $quantifiedProductToAssociate['identifier']
                );
            }

            $quantifiedProduct = new QuantifiedProductProductAssociation();
            $quantifiedProduct->product = $productToAssociate;
            $quantifiedProduct->quantity = $quantifiedProductToAssociate['quantity'];
            $quantifiedProduct->association = $quantifiedAssociation;

            $quantifiedAssociation->addQuantifiedProduct($quantifiedProduct);
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
        foreach ($items as $item) {
            if (!is_string($item['identifier']) || !is_integer($item['quantity'])) {
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
