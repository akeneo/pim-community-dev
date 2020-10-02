<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;

final class ProductValues
{
    /** @var Attribute */
    private $attribute;

    /** @var ChannelLocaleDataCollection */
    private $values;

    public function __construct(Attribute $attribute, ChannelLocaleDataCollection $values)
    {
        $this->attribute = $attribute;
        $this->values = $values;
    }

    public function getAttribute(): Attribute
    {
        return $this->attribute;
    }

    public function getValueByChannelAndLocale(ChannelCode $channelCode, LocaleCode $localeCode)
    {
        return $this->values->getByChannelAndLocale($channelCode, $localeCode);
    }
}
