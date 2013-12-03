<?php

namespace Oro\Bundle\InstallerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class DatabaseConnection extends Constraint
{
    //@codingStandardsIgnoreStart
    public $message = 'Unable to connect to database "%name%". Please check database connection parameters and user permissions.';
    //@codingStandardsIgnoreEnd
}
