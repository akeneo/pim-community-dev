<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Bundle\BaseConnectorBundle\Processor\Denormalization\AbstractProcessor;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Variant group import processor, allows to,
 *  - create / update variant groups
 *  - validate values and save values in template (it erases existing values)
 *  - return the valid variant groups, throw exceptions to skip invalid ones
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupProcessor extends AbstractProcessor
{
    /** @var StandardArrayConverterInterface */
    private $variantConverter;

    /** @var StandardArrayConverterInterface */
    private $productConverter;

    /** @var ObjectUpdaterInterface */
    protected $variantUpdater;

    /** @var ObjectUpdaterInterface */
    protected $productUpdater;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var string */
    protected $groupClass;

    /** @var string */
    protected $productTemplateClass;

    /**
     * @param StandardArrayConverterInterface       $variantConverter
     * @param StandardArrayConverterInterface       $productConverter
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param ObjectUpdaterInterface                $variantUpdater
     * @param ObjectUpdaterInterface                $productUpdater
     * @param ProductBuilderInterface               $productBuilder
     * @param ValidatorInterface                    $validator
     * @param string                                $groupClass
     * @param string                                $productTemplateClass
     */
    public function __construct(
        StandardArrayConverterInterface $variantConverter,
        StandardArrayConverterInterface $productConverter,
        IdentifiableObjectRepositoryInterface $repository,
        ObjectUpdaterInterface $variantUpdater,
        ObjectUpdaterInterface $productUpdater,
        ProductBuilderInterface $productBuilder,
        ValidatorInterface $validator,
        $groupClass,
        $productTemplateClass
    ) {
        parent::__construct($repository);

        $this->variantConverter     = $variantConverter;
        $this->productConverter     = $productConverter;
        $this->variantUpdater       = $variantUpdater;
        $this->productUpdater       = $productUpdater;
        $this->productBuilder       = $productBuilder;
        $this->validator            = $validator;
        $this->groupClass           = $groupClass;
        $this->productTemplateClass = $productTemplateClass;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $convertedItem = $this->convertItemData($item);
        $variantGroup = $this->findOrCreateVariantGroup($convertedItem);

        try {
            $this->updateVariantGroup($variantGroup, $convertedItem);
        } catch (\InvalidArgumentException $exception) {
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validateVariantGroup($variantGroup);
        if ($violations->count() > 0) {
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        return $variantGroup;
    }

    /**
     * @param array $item
     *
     * @return array
     */
    protected function convertItemData(array $item)
    {
        $convertedItem = $this->variantConverter->convert($item);

        if (isset($convertedItem['values'])) {
            $convertedItem['values'] = $this->productConverter->convert($convertedItem['values']);
            unset($convertedItem['values']['enabled']);
        }

        return $convertedItem;
    }

    /**
     * Find or create the variant group
     *
     * @param array $convertedItem
     *
     * @return GroupInterface
     */
    protected function findOrCreateVariantGroup(array $convertedItem)
    {
        if (null === $variantGroup = $this->findObject($this->repository, $convertedItem)) {
            $variantGroup = new $this->groupClass();
        }

        $isExistingGroup = (null !== $variantGroup->getType() && false === $variantGroup->getType()->isVariant());
        if ($isExistingGroup) {
            $this->skipItemWithMessage(
                $convertedItem,
                sprintf('Cannot process group "%s", only variant groups are accepted', $convertedItem['code'])
            );
        }

        return $variantGroup;
    }

    /**
     * Update the variant group fields
     *
     * @param GroupInterface $variantGroup
     * @param array          $convertedItem
     */
    protected function updateVariantGroup(GroupInterface $variantGroup, array $convertedItem)
    {
        $this->variantUpdater->update($variantGroup, $convertedItem);

        if (isset($convertedItem['values'])) {
            $this->updateProductValues($variantGroup, $convertedItem['values']);
        }
    }

    /**
     * @param GroupInterface $variantGroup
     * @param array          $convertedValues
     */
    protected function updateProductValues(GroupInterface $variantGroup, array $convertedValues)
    {
        $values = $this->transformArrayToValues($convertedValues);

        // TODO: remove it when normalizers & setters will be uniformized (PIM-4246)
        foreach ($convertedValues as $code => $arrayValues) {
            foreach ($arrayValues as $index => $value) {
                $convertedValues[$code][$index]['value'] = $value['data'];
                unset($convertedValues[$code][$index]['data']);
            }
        }

        $template = $this->getProductTemplate($variantGroup);
        $template->setValues($values)
            ->setValuesData($convertedValues);

        $variantGroup->setProductTemplate($template);
    }

    /**
     * @param array $convertedValues
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    protected function transformArrayToValues(array $convertedValues)
    {
        $product = $this->productBuilder->createProduct();
        $this->productUpdater->update($product, $convertedValues);

        $values = $product->getValues();
        $values->removeElement($product->getIdentifier());

        return $values;
    }

    /**
     * @param GroupInterface $variantGroup
     *
     * @throws InvalidItemException
     *
     * @return \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    protected function validateVariantGroup(GroupInterface $variantGroup)
    {
        $violations = $this->validator->validate($variantGroup);
        $template = $variantGroup->getProductTemplate();

        if (null !== $template) {
            $values = $variantGroup->getProductTemplate()->getValues();

            foreach ($values as $value) {
                $violations->addAll($this->validator->validate($value));
            }
        }

        return $violations;
    }

    /**
     * @param GroupInterface $variantGroup
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface
     */
    protected function getProductTemplate(GroupInterface $variantGroup)
    {
        if ($variantGroup->getProductTemplate()) {
            $template = $variantGroup->getProductTemplate();
        } else {
            $template = new $this->productTemplateClass();
        }

        return $template;
    }
}
