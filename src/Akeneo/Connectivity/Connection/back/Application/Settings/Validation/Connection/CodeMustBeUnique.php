<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CodeMustBeUnique extends Constraint
{
    public $message = 'akeneo_connectivity.connection.connection.constraint.code.must_be_unique';

    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'connection_code_must_be_unique';
    }
}
