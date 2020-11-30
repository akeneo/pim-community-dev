<?php

namespace Akeneo\Tool\Component\StorageUtils\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for writable directory
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Annotation
 */
class WritableDirectory extends Constraint
{
    /**
     * @var string
     */
    public $message = 'This directory is not writable';

    /**
     * @var string
     */
    public $invalidMessage = 'This directory is not valid';
}
