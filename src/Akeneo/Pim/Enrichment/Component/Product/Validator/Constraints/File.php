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
    public const EXTENSION_NOT_ALLOWED_ERROR = 'ead50d07-6369-48a2-b108-3f7e4eda0048';

    /** @var array */
    public $allowedExtensions = [];

    /** @var string */
    public string $extensionsMessage = 'The %type% file extension is not allowed for the %attribute% attribute. Allowed extensions are %extensions%.';

    /** @var string */
    public $mimeTypeMessage = 'The MIME type is not allowed for %extension% (allowed types: %types%, found %type%).';

    public string $attributeCode = '';

    /** @var string  */
    public $maxSizeMessage = 'The file %file_name% is too large (%file_size% %suffix%). The %attribute% attribute can not exceed %max_file_size% %suffix%.';
}
