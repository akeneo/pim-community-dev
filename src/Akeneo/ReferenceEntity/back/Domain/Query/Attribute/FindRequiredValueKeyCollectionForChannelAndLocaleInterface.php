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

namespace Akeneo\ReferenceEntity\Domain\Query\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

/**
 * Find the list of required keys for the given reference entity, on the given channel & locale.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
interface FindRequiredValueKeyCollectionForChannelAndLocaleInterface
{
    public function __invoke(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        ChannelIdentifier $channelIdentifier,
        LocaleIdentifier $localeIdentifier
    ): ValueKeyCollection;
}
