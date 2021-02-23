<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory;

use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class EditMediaLinkValueCommand extends AbstractEditValueCommand
{
    public string $mediaLink;

    public function __construct(MediaLinkAttribute $attribute, ?string $channel, ?string $locale, string $mediaLink)
    {
        parent::__construct($attribute, $channel, $locale);

        $this->mediaLink = $mediaLink;
    }

    public function normalize(): array
    {
        return [
            'attribute' => (string) $this->attribute->getIdentifier(),
            'channel' => $this->channel,
            'locale' => $this->locale,
            'data' => $this->mediaLink
        ];
    }
}
