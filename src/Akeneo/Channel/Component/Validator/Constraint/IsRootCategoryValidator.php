<?php

namespace Akeneo\Channel\Component\Validator\Constraint;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class IsRootCategoryValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($category, Constraint $constraint)
    {
        if (null === $category) {
            return;
        }

        if ($category instanceof CategoryInterface && null !== $category->getParent()) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('%category%', $category->getCode())
                ->addViolation();
        }
    }
}
