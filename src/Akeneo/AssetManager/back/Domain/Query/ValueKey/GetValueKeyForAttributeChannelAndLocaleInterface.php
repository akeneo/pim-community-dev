<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Query\ValueKey;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKey;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetValueKeyForAttributeChannelAndLocaleInterface
{
    public function fetch(AttributeIdentifier $attributeIdentifier, ChannelIdentifier $channelIdentifier, LocaleIdentifier $localeIdentifier): ValueKey;
}
