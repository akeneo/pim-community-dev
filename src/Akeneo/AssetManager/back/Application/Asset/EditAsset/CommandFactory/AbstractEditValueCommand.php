<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory;

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @api
 */
abstract class AbstractEditValueCommand
{
    public AbstractAttribute $attribute;
    public ?string $channel;
    public ?string $locale;

    public function __construct(AbstractAttribute $attribute, ?string $channel, ?string $locale)
    {
        $this->attribute = $attribute;
        $this->channel = $channel;
        $this->locale = $locale;
    }

    abstract public function normalize(): array;
}
