<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory;

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\NumberAttribute;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class EditNumberValueCommandFactory implements EditValueCommandFactoryInterface
{
    public function supports(AbstractAttribute $attribute, array $normalizedValue): bool
    {
        return
            $attribute instanceof NumberAttribute &&
            '' !== $normalizedValue['data'] &&
            is_string($normalizedValue['data']);
    }

    public function create(AbstractAttribute $attribute, array $normalizedValue): AbstractEditValueCommand
    {
        return new EditNumberValueCommand(
            $attribute,
            $normalizedValue['channel'],
            $normalizedValue['locale'],
            $normalizedValue['data']
        );
    }
}
