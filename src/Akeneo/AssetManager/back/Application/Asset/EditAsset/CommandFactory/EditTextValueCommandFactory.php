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

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditTextValueCommandFactory implements EditValueCommandFactoryInterface
{
    public function supports(AbstractAttribute $abstractAttribute, array $normalizedValue): bool
    {
        return
            $abstractAttribute instanceof TextAttribute &&
            '' !== $normalizedValue['data'] &&
            is_string($normalizedValue['data']);
    }

    public function create(AbstractAttribute $attribute, array $normalizedValue): AbstractEditValueCommand
    {
        $command = new EditTextValueCommand(
            $attribute,
            $normalizedValue['channel'],
            $normalizedValue['locale'],
            $normalizedValue['data']
        );

        return $command;
    }
}
