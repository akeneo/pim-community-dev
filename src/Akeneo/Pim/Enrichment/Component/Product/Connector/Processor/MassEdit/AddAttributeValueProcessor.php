<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\CheckAttributeEditable;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyAdderInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Processor to add attribute value in a mass edit (for multi select attributes).
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddAttributeValueProcessor extends AbstractProcessor
{
    /** @var ValidatorInterface */
    protected $productValidator;

    /** @var ValidatorInterface */
    protected $productModelValidator;

    /** @var PropertyAdderInterface */
    private $propertyAdder;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    /** @var CheckAttributeEditable */
    protected $checkAttributeEditable;

    /** @var array */
    protected $supportedTypes;

    /**
     * @param ValidatorInterface                    $productValidator
     * @param ValidatorInterface                    $productModelValidator
     * @param PropertyAdderInterface                $propertyAdder
     * @param IdentifiableObjectRepositoryInterface $attributeRepository
     * @param CheckAttributeEditable                $checkAttributeEditable
     * @param array                                 $supportedTypes
     */
    public function __construct(
        ValidatorInterface $productValidator,
        ValidatorInterface $productModelValidator,
        PropertyAdderInterface $propertyAdder,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        CheckAttributeEditable $checkAttributeEditable,
        array $supportedTypes
    ) {
        $this->productValidator = $productValidator;
        $this->productModelValidator = $productModelValidator;
        $this->propertyAdder = $propertyAdder;
        $this->attributeRepository = $attributeRepository;
        $this->checkAttributeEditable = $checkAttributeEditable;
        $this->supportedTypes = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function process($entity)
    {
        $actions = $this->getConfiguredActions();

        if (!$this->isEntityEditable($entity)) {
            $this->stepExecution->incrementSummaryInfo('skipped_products');

            return null;
        }

        $filteredValues = $this->extractValuesToUpdate($entity, $actions[0]);

        if (empty($filteredValues)) {
            $this->stepExecution->incrementSummaryInfo('skipped_products');

            return null;
        }

        $entity = $this->updateEntity($entity, $filteredValues);
        if (!$this->isValid($entity)) {
            $this->stepExecution->incrementSummaryInfo('skipped_products');

            return null;
        }

        return $entity;
    }

    /**
     * Set data from $actions to the given $entity
     *
     * @param EntityWithFamilyInterface $entity
     * @param array                     $filteredValues
     *
     * @return EntityWithFamilyInterface
     */
    protected function updateEntity(EntityWithFamilyInterface $entity, array $filteredValues): EntityWithFamilyInterface
    {
        foreach ($filteredValues as $attributeCode => $values) {
            foreach ($values as $value) {
                $this->propertyAdder->addData(
                    $entity,
                    $attributeCode,
                    $value['data'],
                    [
                        'locale' => $value['locale'],
                        'scope' => $value['scope'],
                    ]);
            }
        }

        return $entity;
    }

    /**
     * @param EntityWithFamilyInterface $entity
     * @param string                    $attributeCode
     *
     * @return bool
     *
     * @throws \Exception
     */
    protected function isAttributeEditable(EntityWithFamilyInterface $entity, string $attributeCode): bool
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

        $isEditable = $this->checkAttributeEditable->isEditable($entity, $attribute);
        $hasCorrectType = in_array($attribute->getType(), $this->supportedTypes);

        return $isEditable && $hasCorrectType;
    }

    /**
     * Validate the entity
     *
     * @param EntityWithFamilyInterface $entity
     *
     * @return bool
     */
    protected function isValid(EntityWithFamilyInterface $entity): bool
    {
        if ($entity instanceof ProductInterface) {
            $violations = $this->productValidator->validate($entity);
        } else {
            $violations = $this->productModelValidator->validate($entity);
        }
        $this->addWarningMessage($violations, $entity);

        return 0 === $violations->count();
    }

    /**
     * Sadly, this is override in Enterprise Edition to check the permissions of the entity.
     *
     * @param EntityWithFamilyInterface $entity
     *
     * @return bool
     */
    protected function isEntityEditable(EntityWithFamilyInterface $entity): bool
    {
        return true;
    }

    /**
     * @param EntityWithFamilyInterface $entity
     */
    protected function addWarning(EntityWithFamilyInterface $entity): void
    {
        $this->stepExecution->addWarning(
            'pim_enrich.mass_edit_action.edit-common-attributes.message.no_valid_attribute',
            [],
            new DataInvalidItem(
                [
                    'class'  => ClassUtils::getClass($entity),
                    'id'     => $entity->getId(),
                    'string' => $entity instanceof ProductInterface ? $entity->getIdentifier() : $entity->getCode(),
                ]
            )
        );
    }

    /**
     * Actions should look like that
     *
     * $actions =
     * [
     *      'normalized_values' => [
     *          'name' => [
     *              [
     *                  'locale' => null,
     *                  'scope'  => null,
     *                  'data' => 'The name'
     *              ]
     *          ],
     *          'description' => [
     *              [
     *                  'locale' => 'en_US',
     *                  'scope' => 'ecommerce',
     *                  'data' => 'The description for en_US ecommerce'
     *              ]
     *          ]
     *      ]
     * ]
     *
     * @param EntityWithFamilyInterface $entity
     * @param array                     $actions
     *
     * @return array
     */
    private function extractValuesToUpdate(EntityWithFamilyInterface $entity, array $actions): array
    {
        $filteredValues = [];
        $normalizedValues = $actions['normalized_values'];
        foreach ($normalizedValues as $attributeCode => $values) {
            if ($this->isAttributeEditable($entity, $attributeCode)) {
                $filteredValues[$attributeCode] = $values;
            }
        }

        return $filteredValues;
    }
}
