<?php

namespace Pim\Bundle\BatchBundle\Item\Validator;

/**
 * Interface to validate objects
 *
 * Inspired by Spring Batch  org.springframework.batch.item.validator.Validator
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
interface ValidatorInterface
{
    /**
     * Method used to validate if the value is valid
     *
     * @param mixed $value object to be validated
     *
     * @throws ValidationException
     */
    public function validate($value);
}
