<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;

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
class VariantGroupProcessor extends GroupProcessor
{
    /**
     * Find or create the variant group
     *
     * @param array $convertedItem
     *
     * @return GroupInterface
     */
    protected function findOrCreateGroup(array $convertedItem)
    {
        if (null === $variantGroup = $this->findObject($this->repository, $convertedItem)) {
            $variantGroup = $this->groupFactory->createGroup($convertedItem['type']);
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
     * @param GroupInterface $group
     *
     * @throws InvalidItemException
     *
     * @return \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    protected function validateGroup(GroupInterface $group)
    {
        $violations = $this->validator->validate($group);
        $template = $group->getProductTemplate();

        if (null !== $template) {
            $values = $group->getProductTemplate()->getValues();

            foreach ($values as $value) {
                $violations->addAll($this->validator->validate($value));
            }
        }

        return $violations;
    }
}
