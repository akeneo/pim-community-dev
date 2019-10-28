<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\CheckAttributeEditable;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Processor to add product value in a mass edit
 *
 * @author    Julien.* <julien@akeneo.com>|<j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditAttributesProcessor extends AbstractProcessor
{
    /** @var ValidatorInterface */
    protected $productValidator;

    /** @var ValidatorInterface */
    protected $productModelValidator;

    /** @var ObjectUpdaterInterface */
    protected $productUpdater;

    /** @var ObjectUpdaterInterface */
    protected $productModelUpdater;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    /** @var CheckAttributeEditable */
    protected $checkAttributeEditable;

    /** @var FilterInterface */
    protected $productEmptyValuesFilter;

    /** @var FilterInterface */
    protected $productModelEmptyValuesFilter;

    public function __construct(
        ValidatorInterface $productValidator,
        ValidatorInterface $productModelValidator,
        ObjectUpdaterInterface $productUpdater,
        ObjectUpdaterInterface $productModelUpdater,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        CheckAttributeEditable $checkAttributeEditable,
        FilterInterface $productEmptyValuesFilter,
        FilterInterface $productModelEmptyValuesFilter
    ) {
        $this->productValidator = $productValidator;
        $this->productModelValidator = $productModelValidator;
        $this->productUpdater = $productUpdater;
        $this->productModelUpdater = $productModelUpdater;
        $this->attributeRepository = $attributeRepository;
        $this->checkAttributeEditable = $checkAttributeEditable;
        $this->productEmptyValuesFilter = $productEmptyValuesFilter;
        $this->productModelEmptyValuesFilter = $productModelEmptyValuesFilter;
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
        if ($entity instanceof ProductInterface) {
            $filteredValues = $this->productEmptyValuesFilter->filter($entity, ['values' => $filteredValues]);
        } else {
            $filteredValues = $this->productModelEmptyValuesFilter->filter($entity, ['values' => $filteredValues]);
        }

        if (empty($filteredValues['values'])) {
            $this->stepExecution->incrementSummaryInfo('skipped_products');

            return null;
        }

        $entity = $this->updateEntity($entity, $filteredValues['values']);
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
        if ($entity instanceof ProductInterface) {
            $this->productUpdater->update($entity, ['values' => $filteredValues]);
        } else {
            $this->productModelUpdater->update($entity, ['values' => $filteredValues]);
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

        return $this->checkAttributeEditable->isEditable($entity, $attribute);
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
