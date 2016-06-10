<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\Batch\Item\InvalidItemException;
use Pim\Component\Catalog\Model\GroupInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

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
     * @param array $item
     *
     * @return GroupInterface
     */
    protected function findOrCreateGroup(array $item)
    {
        if (null === $variantGroup = $this->findObject($this->repository, $item)) {
            $variantGroup = $this->groupFactory->createGroup($item['type']);
        }

        $isExistingGroup = (null !== $variantGroup->getType() && false === $variantGroup->getType()->isVariant());
        if ($isExistingGroup) {
            $this->skipItemWithMessage(
                $item,
                sprintf('Cannot process group "%s", only variant groups are accepted', $item['code'])
            );
        }

        return $variantGroup;
    }

    /**
     * @param GroupInterface $group
     *
     * @throws InvalidItemException
     *
     * @return ConstraintViolationListInterface
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
