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

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;

/**
 * Create a {@see Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\AppendOptionCollectionValueCommand}
 *
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
class AppendOptionCollectionValueCommandFactory implements EditValueCommandFactoryInterface
{
    public function supports(AbstractAttribute $attribute, array $normalizedValue): bool
    {
        return
            $attribute instanceof OptionCollectionAttribute
            && (is_array($normalizedValue['data']) || null === $normalizedValue['data'])
            && isset($normalizedValue['action'])
            && 'append' === $normalizedValue['action'];
    }

    public function create(AbstractAttribute $attribute, array $normalizedValue): AbstractEditValueCommand
    {
        $command = new AppendOptionCollectionValueCommand(
            $attribute,
            $normalizedValue['channel'],
            $normalizedValue['locale'],
            $normalizedValue['data'] ?? []
        );

        return $command;
    }
}
