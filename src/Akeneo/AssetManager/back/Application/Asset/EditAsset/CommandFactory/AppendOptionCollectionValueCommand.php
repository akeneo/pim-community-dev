<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory;

use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;

/**
 * Command to append options to an "option collection" attribute.
 *
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
class AppendOptionCollectionValueCommand extends AbstractEditValueCommand
{
    /** @var string[] */
    public array $optionCodes;

    public function __construct(OptionCollectionAttribute $attribute, ?string $channel, ?string $locale, array $optionCodes)
    {
        parent::__construct($attribute, $channel, $locale);

        $this->optionCodes = $optionCodes;
    }

    public function normalize(): array
    {
        return [
            'attribute' => (string) $this->attribute->getIdentifier(),
            'channel' => $this->channel,
            'locale' => $this->locale,
            'data' => $this->optionCodes,
            'action' => 'append'
        ];
    }
}
