<?php

namespace Oro\Bundle\FormBundle\Validator;

use Symfony\Component\Validator\Constraint;

class ConstraintFactory
{
    /**
     * @param string $name
     * @param mixed $options
     * @return Constraint
     */
    public function create($name, $options)
    {
        if (strpos($name, '\\') !== false && class_exists($name)) {
            $className = (string) $name;
        } else {
            $className = 'Symfony\\Component\\Validator\\Constraints\\'.$name;
        }

        return new $className($options);
    }
}
