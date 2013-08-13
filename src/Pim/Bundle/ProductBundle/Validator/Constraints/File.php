<?php

namespace Pim\Bundle\ProductBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraints\File as BaseFile;

/**
 * Constraint
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Annotation
 */
class File extends BaseFile
{
    public $allowedExtensions = array();
    public $extensionsMessage = 'The file extension is not allowed (allowed extensions: {{ extensions }}).';
}
