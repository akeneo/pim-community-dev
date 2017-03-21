<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Validator;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AllowedExtensionsValidator extends ConstraintValidator
{
    /** @var string[] */
    protected $validExtensions;

    /**
     * @param string[] $validExtensions
     */
    public function __construct(array $validExtensions)
    {
        $this->validExtensions = $validExtensions;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if ('' === $value || null === $value) {
            return;
        }

        $extensions = explode(',', $value);

        foreach ($extensions as $extension) {
            if (!in_array($extension, $this->validExtensions)) {
                $this->context
                    ->buildViolation($constraint->message)
                    ->setParameter('%extension%', $extension)
                    ->setParameter('%valid_extensions%', implode(', ', $this->validExtensions))
                    ->addViolation();
            }
        }
    }
}
