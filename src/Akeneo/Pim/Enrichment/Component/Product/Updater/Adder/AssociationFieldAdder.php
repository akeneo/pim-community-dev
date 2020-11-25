<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Adder;

use Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationFieldAdder extends AbstractFieldAdder
{
    protected IdentifiableObjectRepositoryInterface $productRepository;

    protected IdentifiableObjectRepositoryInterface $productModelRepository;

    protected IdentifiableObjectRepositoryInterface $groupRepository;

    private MissingAssociationAdder $missingAssociationAdder;

    private AssociationTypeRepositoryInterface $associationTypeRepository;

    public function __construct(
        IdentifiableObjectRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        IdentifiableObjectRepositoryInterface $groupRepository,
        MissingAssociationAdder $missingAssociationAdder,
        AssociationTypeRepositoryInterface $associationTypeRepository,
        array $supportedFields
    ) {
        $this->productRepository = $productRepository;
        $this->productModelRepository = $productModelRepository;
        $this->groupRepository = $groupRepository;
        $this->missingAssociationAdder = $missingAssociationAdder;
        $this->supportedFields = $supportedFields;
        $this->associationTypeRepository = $associationTypeRepository;
    }

    /**
     * {@inheritdoc}
     *
     * Expected data input format :
     * {
     *     "XSELL": {
     *         "groups": ["group1", "group2"],
     *         "products": ["AKN_TS1", "AKN_TSH2"],
     *         "product_models": ["amor"]
     *     },
     *     "UPSELL": {
     *         "groups": ["group3", "group4"],
     *         "products": ["AKN_TS3", "AKN_TSH4"],
     *         "product_models": ["amor"]
     *     },
     * }
     */
    public function addFieldData($entity, $field, $data, array $options = []): void
    {
        if (!$entity instanceof EntityWithAssociationsInterface) {
            throw InvalidObjectException::objectExpected($entity, EntityWithAssociationsInterface::class);
        }

        $this->checkData($field, $data);
        $this->missingAssociationAdder->addMissingAssociations($entity);
        $this->addProductsAndGroupsToAssociations($entity, $data);
    }

    /**
     * Add products and groups to associations
     *
     * @param ProductInterface|ProductModelInterface $entity
     * @param mixed                                  $data
     *
     * @throws InvalidPropertyException
     */
    protected function addProductsAndGroupsToAssociations($entity, $data): void
    {
        foreach ($data as $typeCode => $items) {
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
            $this->addAssociatedProducts($associationType,$items['products'] ?? [], $entity);
            $this->addAssociatedGroups($associationType, $items['groups'] ?? [], $entity);
            $this->addAssociatedProductModels($associationType, $items['product_models'] ?? [], $entity);
        }
    }

    /**
     * @param AssociationTypeInterface $associationType
     * @param array $productsIdentifiers
     * @param ProductInterface|ProductModelInterface $entity
     *
     * @throws InvalidPropertyException
     */
    protected function addAssociatedProducts(
        AssociationTypeInterface $associationType,
        array $productsIdentifiers,
        $entity
    ): void {
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
            $entity->addAssociatedProduct($associatedProduct, $associationType->getCode());
        }
    }

    /**
     * @param AssociationTypeInterface $associationType
     * @param array $productModelsIdentifiers
     * @param ProductInterface|ProductModelInterface $entity
     *
     * @throws InvalidPropertyException
     */
    protected function addAssociatedProductModels(
        AssociationTypeInterface $associationType,
        array $productModelsIdentifiers,
        $entity
    ): void {
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
            $entity->addAssociatedProduct($associatedProductModel, $associationType->getCode());
        }
    }

    /**
     * @param AssociationTypeInterface $associationType
     * @param array $groupsCodes
     * @param ProductInterface|ProductModelInterface $entity
     *
     * @throws InvalidPropertyException
     */
    protected function addAssociatedGroups(AssociationTypeInterface $associationType, array $groupsCodes, $entity): void
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
            $entity->addAssociatedGroup($associatedGroup, $associationType->getCode());
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
            $assocTypeCode = (string) $assocTypeCode;
            $this->checkAssociationData($field, $data, $assocTypeCode, $items);
        }
    }

    protected function checkAssociationData(string $field, array $data, string $assocTypeCode, $items): void
    {
        if (!is_array($items) || !is_string($assocTypeCode) ||
            (!isset($items['products']) && !isset($items['groups']) && !isset($items['product_models']))) {
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

    protected function checkAssociationItems(string $field, string $assocTypeCode, array $data, array $items): void
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
