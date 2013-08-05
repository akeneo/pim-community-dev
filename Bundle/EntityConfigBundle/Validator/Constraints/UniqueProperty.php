<?php

namespace Oro\Bundle\EntityConfigBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class UniqueProperty extends Constraint
{
    public $message = 'This value is already used.';
    public $service = 'doctrine.orm.validator.unique';
    public $em = null;
    public $repositoryMethod = 'findBy';
    public $fields = array();
    public $errorPath = null;
    public $ignoreNull = true;

    public function getRequiredOptions()
    {
        return array('fields');
    }

    /**
     * The validator must be defined as a service with this name.
     *
     * @return string
     */
    public function validatedBy()
    {
        return $this->service;
    }

    public function getDefaultOption()
    {
        return 'fields';
    }
}
