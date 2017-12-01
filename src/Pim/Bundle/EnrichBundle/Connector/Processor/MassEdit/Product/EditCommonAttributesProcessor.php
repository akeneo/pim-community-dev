<?php

namespace Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor;
use Pim\Component\Catalog\Model\EntityWithFamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Processor to add product value in a mass edit
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditCommonAttributesProcessor extends AbstractProcessor
{
    /** @var ValidatorInterface */
    protected $validator;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var array */
    protected $skippedAttributes = [];

    /** @var ObjectUpdaterInterface */
    protected $productUpdater;

    /** @var ObjectUpdaterInterface */
    protected $productModelUpdater;

    /** @var ObjectDetacherInterface */
    protected $detacher;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param ValidatorInterface                    $validator
     * @param ProductRepositoryInterface            $productRepository
     * @param ObjectUpdaterInterface                $productUpdater
     * @param ObjectUpdaterInterface                $productModelUpdater
     * @param ObjectDetacherInterface               $productDetacher
     * @param IdentifiableObjectRepositoryInterface $attributeRepository
     */
    public function __construct(
        ValidatorInterface $validator,
        ProductRepositoryInterface $productRepository,
        ObjectUpdaterInterface $productUpdater,
        ObjectUpdaterInterface $productModelUpdater,
        ObjectDetacherInterface $productDetacher,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->validator = $validator;
        $this->productRepository = $productRepository;
        $this->productUpdater = $productUpdater;
        $this->productModelUpdater = $productModelUpdater;
        $this->detacher = $productDetacher;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function process($entity)
    {
        $actions = $this->getConfiguredActions();

        if (!$this->isEntityEditable($entity)) {
            $this->stepExecution->incrementSummaryInfo('skipped_products');
            $this->detacher->detach($entity);

            return null;
        }

        $entity = $this->updateEntity($entity, $actions[0]);
        if (null !== $entity && !$this->isProductValid($entity)) {
            $this->stepExecution->incrementSummaryInfo('skipped_products');
            $this->detacher->detach($entity);

            return null;
        }

        return $entity;
    }

    /**
     * Set data from $actions to the given $product
     *
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
     * @throws \LogicException
     *
     * @return ProductInterface $product
     */
    protected function updateEntity(EntityWithFamilyInterface $entity, array $actions)
    {
        $normalizedValues = $actions['normalized_values'];
        $filteredValues = [];

        foreach ($normalizedValues as $attributeCode => $values) {
            /**
             * We don't call that method directly on the product model because it hydrates
             * lot of models and it causes memory leak...
             */
            if ($this->isAttributeEditable($entity, $attributeCode)) {
                $filteredValues['values'][$attributeCode] = $values;
            }
        }

        if (empty($filteredValues)) {
            $this->stepExecution->incrementSummaryInfo('skipped_products');
            $this->addWarning($entity);
            $this->detacher->detach($entity);

            return null;
        }

        if ($entity instanceof ProductInterface) {
            $this->productUpdater->update($entity, $filteredValues);
        } else {
            $this->productModelUpdater->update($entity, $filteredValues);
        }

        return $entity;
    }

    /**
     * @param EntityWithFamilyInterface $entity
     * @param string                    $attributeCode
     *
     * @return bool
     */
    protected function isAttributeEditable(EntityWithFamilyInterface $entity, string $attributeCode)
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
        $family = $entity->getFamily();

        if (!$family->hasAttribute($attribute)) {
            return false;
        }

        if ($entity instanceof VariantProductInterface || $entity instanceof ProductModelInterface) {
            $familyVariant = $entity->getFamilyVariant();
            $level = $entity->getVariationLevel();
            $attributeSet = $familyVariant->getVariantAttributeSet($level);

            return $attributeSet->hasAttribute($attribute);
        }

        return true;
    }

    /**
     * Validate the product
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function isProductValid(ProductInterface $product)
    {
        //TODO: use pm validator
        $violations = $this->validator->validate($product);
        $this->addWarningMessage($violations, $product);

        return 0 === $violations->count();
    }

    /**
     * @param EntityWithFamilyInterface $entity
     *
     * @return bool
     */
    protected function isEntityEditable(EntityWithFamilyInterface $entity)
    {
        return true;
    }

    /**
     * @param EntityWithFamilyInterface $entity
     */
    protected function addWarning(EntityWithFamilyInterface $entity)
    {
        /*
         * We don't give the product to addWarning because we don't want that step executor
         * calls the toString method which hydrate lot of model
         */
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
}
