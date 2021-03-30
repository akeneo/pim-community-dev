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

use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditTextValueCommand extends AbstractEditValueCommand
{
    public string $text;

    public function __construct(TextAttribute $attribute, ?string $channel, ?string $locale, string $text)
    {
        parent::__construct($attribute, $channel, $locale);

        $this->text = $text;
    }

    public function normalize(): array
    {
        return [
            'attribute' => (string) $this->attribute->getIdentifier(),
            'channel' => $this->channel,
            'locale' => $this->locale,
            'data' => $this->text
        ];
    }
}
