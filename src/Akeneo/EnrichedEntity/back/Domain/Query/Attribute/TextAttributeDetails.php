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

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TextAttributeDetails extends AbstractAttributeDetails
{
    public const ATTRIBUTE_TYPE = 'text';
    public const MAX_LENGTH = 'max_length';

    /** @var AttributeMaxLength */
    public $maxLength;

    public function normalize(): array
    {
        return array_merge(
            parent::normalize(),
            [
                self::MAX_LENGTH => $this->maxLength->intValue(),
                self::TYPE => self::ATTRIBUTE_TYPE
            ]
        );
    }
}
