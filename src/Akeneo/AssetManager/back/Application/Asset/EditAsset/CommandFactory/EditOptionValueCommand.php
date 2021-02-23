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

namespace Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory;

use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditOptionValueCommand extends AbstractEditValueCommand
{
    public string $optionCode;

    public function __construct(OptionAttribute $attribute, ?string $channel, ?string $locale, string $optionCode)
    {
        parent::__construct($attribute, $channel, $locale);

        $this->optionCode = $optionCode;
    }

    public function normalize(): array
    {
        return [
            'attribute' => (string) $this->attribute->getIdentifier(),
            'channel' => $this->channel,
            'locale' => $this->locale,
            'data' => $this->optionCode
        ];
    }
}
