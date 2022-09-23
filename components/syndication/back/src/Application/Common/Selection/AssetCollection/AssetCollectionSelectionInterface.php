<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Syndication\Application\Common\Selection\AssetCollection;

use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;

interface AssetCollectionSelectionInterface extends SelectionInterface
{
    public function getSeparator(): string;
    public function getAssetFamilyCode(): string;
    public function getAttributeCode(): string;
}
