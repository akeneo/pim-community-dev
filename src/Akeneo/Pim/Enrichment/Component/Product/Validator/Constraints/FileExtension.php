<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileExtension extends Constraint
{
    /** @var array */
    public $allowedExtensions = [];

    /** @var string */
    public $extensionsMessage = 'The file extension is not allowed (allowed extensions: %extensions%).';
}
