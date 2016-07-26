<?php

namespace Pim\Bundle\ImportExportBundle\Validator\Constraints;

use Pim\Component\Catalog\Query\ProductQueryBuilderFactory;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FilterDataValidator extends ConstraintValidator
{
    /** @var ProductQueryBuilderFactory */
    protected $pqbFactory;

    /**
     * @param ProductQueryBuilderFactory $pqbFactory
     */
    public function __construct(ProductQueryBuilderFactory $pqbFactory)
    {
        $this->pqbFactory = $pqbFactory;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!isset($value['data'])) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }

        try {
            $pqb = $this->pqbFactory->create($value['data']);
            $pqb->execute();
        } catch (\Exception $e) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
