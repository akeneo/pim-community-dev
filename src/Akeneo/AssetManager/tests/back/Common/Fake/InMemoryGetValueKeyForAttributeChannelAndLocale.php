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

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\AssetManager\Domain\Query\ValueKey\GetValueKeyForAttributeChannelAndLocaleInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryGetValueKeyForAttributeChannelAndLocale implements GetValueKeyForAttributeChannelAndLocaleInterface
{
    /**
     * {@inheritdoc}
     */
    public function fetch(
        AttributeIdentifier $attributeIdentifier,
        ChannelIdentifier $channelIdentifier,
        LocaleIdentifier $localeIdentifier
    ): ValueKey {
        throw new NotImplementedException('__invoke');
    }
}
