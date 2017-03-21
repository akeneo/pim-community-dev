<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FileAllowedExtensions extends Constraint
{
    public $message = '"%extension%" is not a supported file extension. Valid extensions are: %valid_extensions%';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_file_allowed_extensions_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
