<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Bundle\BaseConnectorBundle\Processor\Denormalization\AbstractProcessor;
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
    protected $variantConverter;

    /** @var ObjectUpdaterInterface */
    protected $variantUpdater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var string */
    protected $groupClass;

    /**
     * @param StandardArrayConverterInterface       $variantConverter
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param ObjectUpdaterInterface                $variantUpdater
     * @param ValidatorInterface                    $validator
     * @param string                                $groupClass
     */
    public function __construct(
        StandardArrayConverterInterface $variantConverter,
        IdentifiableObjectRepositoryInterface $repository,
        ObjectUpdaterInterface $variantUpdater,
        ValidatorInterface $validator,
        $groupClass
    ) {
        parent::__construct($repository);

        $this->variantConverter = $variantConverter;
        $this->variantUpdater   = $variantUpdater;
        $this->validator        = $validator;
        $this->groupClass       = $groupClass;
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
        return $this->variantConverter->convert($item);
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
}
