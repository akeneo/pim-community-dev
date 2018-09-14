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

namespace Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditFileValueCommandFactory implements EditRecordValueCommandFactoryInterface
{
    public function supports(AbstractAttribute $attribute): bool
    {
        return $attribute instanceof ImageAttribute;
    }

    public function create(array $normalizedValue, AbstractAttribute $attribute): EditFileValueCommand
    {
        $editFileValueCommand = new EditFileValueCommand();
        $editFileValueCommand->attribute = $attribute;
        $editFileValueCommand->channel = $normalizedValue['channel'];
        $editFileValueCommand->locale = $normalizedValue['locale'];
        $editFileValueCommand->data = $normalizedValue['data'];

        return $editFileValueCommand;
    }
}
