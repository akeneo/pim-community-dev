<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Pim\Component\Catalog\Model\GroupInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
class VariantGroupProcessor extends SimpleProcessor
{
    /**
     * {@inheritdoc}
     */
    protected function getViolations($group)
    {
        $violations = parent::getViolations($group);

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
