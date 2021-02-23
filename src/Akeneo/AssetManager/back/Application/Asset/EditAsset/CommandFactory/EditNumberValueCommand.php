<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory;

use Akeneo\AssetManager\Domain\Model\Attribute\NumberAttribute;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class EditNumberValueCommand extends AbstractEditValueCommand
{
    public string $number;

    public function __construct(NumberAttribute $attribute, ?string $channel, ?string $locale, string $number)
    {
        parent::__construct($attribute, $channel, $locale);

        $this->number = $number;
    }

    public function normalize(): array
    {
        return [
            'attribute' => (string) $this->attribute->getIdentifier(),
            'channel' => $this->channel,
            'locale' => $this->locale,
            'data' => $this->number
        ];
    }
}
