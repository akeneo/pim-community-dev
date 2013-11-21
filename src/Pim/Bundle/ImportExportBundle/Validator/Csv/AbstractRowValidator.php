<?php

namespace Pim\Bundle\ImportExportBundle\Validator\Csv;

use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;

/**
 * Abstract csv row validator
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AbstractRowValidator implements RowValidatorInterface
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var array
     */
    protected $constraints = array();

    /**
     * Constructor
     *
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidItemException
     */
    public function validate(array $data)
    {
        $fieldConstraints = $this->getFieldConstraints();
        foreach ($fieldConstraints as $field => $constraints) {
            foreach ($constraints as $constraint) {
                $violations = $this->validator->validateValue($data[$field], $constraint);
                if (count($violations) > 0) {
                    $messages = array();
                    foreach ($violations as $violation) {
                        $messages[]= (string) $violation;
                    }

                    throw new InvalidItemException(implode(', ', $messages), $data);
                }
            }
        }
    }

    /**
     * Return not blank constraint
     *
     * @param string $field
     *
     * @return Constraint
     */
    protected function buildNotBlankConstraint($field)
    {
        return new NotBlank(array('message' => sprintf('The value "%s" should not be blank.', $field)));
    }

    /**
     * Return constrain associated to a boolean field validation
     *
     * @param string $field
     *
     * @return Constraint
     */
    protected function buildBooleanConstraint($field)
    {
        return new Regex(
            array(
                'pattern' => '/^[0-1]$/',
                'message' => sprintf('The value "%s" must be 0 or 1.', $field)
            )
        );
    }
}
