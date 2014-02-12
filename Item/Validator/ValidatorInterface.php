<?php

namespace Akeneo\Bundle\BatchBundle\Item\Validator;

/**
 * Interface to validate objects
 *
 * Inspired by Spring Batch  org.springframework.batch.item.validator.Validator
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
