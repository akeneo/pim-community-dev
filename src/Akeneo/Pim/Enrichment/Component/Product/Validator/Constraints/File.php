<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraints\File as BaseFile;

/**
 * Constraint
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Annotation
 */
class File extends BaseFile
{
    /** @var array */
    public $allowedExtensions = [];

    /** @var string */
    public $extensionsMessage = 'The file extension is not allowed (allowed extensions: %extensions%).';

    /** @var string */
    public $mimeTypeMessage = 'The MIME type is not allowed for %extension% (allowed types: %types%, found %type%).';
}
