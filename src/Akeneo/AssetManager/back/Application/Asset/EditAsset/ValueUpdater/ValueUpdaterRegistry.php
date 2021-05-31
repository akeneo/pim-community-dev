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

namespace Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\AbstractEditValueCommand;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ValueUpdaterRegistry implements ValueUpdaterRegistryInterface
{
    /** @var ValueUpdaterInterface[]  */
    private array $updaters = [];

    public function register(ValueUpdaterInterface $valueUpdater): void
    {
        $this->updaters[] = $valueUpdater;
    }

    public function getUpdater(AbstractEditValueCommand $command): ValueUpdaterInterface
    {
        foreach ($this->updaters as $updater) {
            if ($updater->supports($command)) {
                return $updater;
            }
        }

        throw new \RuntimeException(
            sprintf(
                'There was no updater found to update the value of the attribute "%s"',
                $command->attribute->getIdentifier()->normalize()
            )
        );
    }
}
