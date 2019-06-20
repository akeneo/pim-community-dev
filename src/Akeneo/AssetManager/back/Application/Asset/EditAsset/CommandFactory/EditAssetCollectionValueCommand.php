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

use Akeneo\AssetManager\Domain\Model\Attribute\AssetCollectionAttribute;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditAssetCollectionValueCommand extends AbstractEditValueCommand
{
    /** @var string[] */
    public $assetCodes;

    public function __construct(AssetCollectionAttribute $attribute, ?string $channel, ?string $locale, array $assetCodes)
    {
        parent::__construct($attribute, $channel, $locale);

        $this->assetCodes = $assetCodes;
    }
}
