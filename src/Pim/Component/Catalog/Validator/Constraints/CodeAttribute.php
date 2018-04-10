<?php
declare(strict_types=1);

namespace Pim\Component\Catalog\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CodeAttribute extends Constraint
{
    /** @var string */
    public $messageAttributeCode = 'This code isn\'t valid.';

    /** @var string */
    public $propertyPath = 'code';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'pim_attribute_code_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
