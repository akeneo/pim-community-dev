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

use Akeneo\AssetManager\Domain\Model\Attribute\AssetAttribute;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditAssetValueCommand extends AbstractEditValueCommand
{
    /** @var string */
    public $assetCode;

    public function __construct(AssetAttribute $attribute, ?string $channel, ?string $locale, string $assetCode)
    {
        parent::__construct($attribute, $channel, $locale);

        $this->assetCode = $assetCode;
    }
}
