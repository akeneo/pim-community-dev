<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\AbstractEditValueCommand;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 * @api
 */
interface ValueUpdaterInterface
{
    public function supports(AbstractEditValueCommand $command): bool;

    public function __invoke(Asset $asset, AbstractEditValueCommand $command): void;
}
