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

namespace Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditTextValueCommandFactory implements EditValueCommandFactoryInterface
{
    public function supports(AbstractAttribute $abstractAttribute): bool
    {
        return $abstractAttribute instanceof TextAttribute;
    }

    public function create(AbstractAttribute $attribute, $normalizedCommand)
    {
        $command = new EditTextValueCommand();
        $command->attribute = $attribute;
        $command->channel = $normalizedCommand['channel'];
        $command->locale = $normalizedCommand['locale'];
        $command->text = $normalizedCommand['data'];

        return $command;
    }
}
