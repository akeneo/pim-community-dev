<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\EnrichedEntity\Domain\Query\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxFileSize;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImageAttributeDetails extends AbstractAttributeDetails
{
    public const ATTRIBUTE_TYPE = 'image';
    public const MAX_FILE_SIZE = 'max_file_size';
    public const ALLOWED_EXTENSIONS = 'allowed_extensions';

    /** @var AttributeMaxFileSize */
    public $maxFileSize;

    /** @var AttributeAllowedExtensions */
    public $allowedExtensions;

    public function normalize(): array
    {
        return array_merge(
            parent::normalize(),
            [
                self::MAX_FILE_SIZE      => $this->maxFileSize->normalize(),
                self::ALLOWED_EXTENSIONS => $this->allowedExtensions->normalize(),
                self::TYPE => self::ATTRIBUTE_TYPE
            ]
        );
    }
}
