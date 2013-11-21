<?php

namespace Pim\Bundle\ImportExportBundle\Validator\Csv;

use Symfony\Component\Validator\ValidatorInterface;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;

/**
 * Abstract csv row validator
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AbstractValidator implements RowValidatorInterface
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
}
