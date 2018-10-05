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
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditStoredFileValueCommandFactory implements EditValueCommandFactoryInterface
{
    public function supports(AbstractAttribute $attribute, array $normalizedValue): bool
    {
        if (!key_exists('data', $normalizedValue) || !is_array($normalizedValue['data'])) {
            return false;
        }

        $hasExpectedFields = (5 === count($normalizedValue['data'])) &&
            key_exists('filePath', $normalizedValue['data']) &&
            key_exists('originalFilename', $normalizedValue['data']) &&
            key_exists('size', $normalizedValue['data']) &&
            key_exists('mimeType', $normalizedValue['data']) &&
            key_exists('extension', $normalizedValue['data']);

        return $attribute instanceof ImageAttribute && $hasExpectedFields;
    }

    public function create(AbstractAttribute $attribute, array $normalizedValue): AbstractEditValueCommand
    {
        $command = new EditStoredFileValueCommand();
        $command->attribute = $attribute;
        $command->channel = $normalizedValue['channel'];
        $command->locale = $normalizedValue['locale'];

        $command->filePath = $normalizedValue['data']['filePath'];
        $command->originalFilename = $normalizedValue['data']['originalFilename'];
        $command->size = $normalizedValue['data']['size'];
        $command->mimeType = $normalizedValue['data']['mimeType'];
        $command->extension = $normalizedValue['data']['extension'];

        return $command;
    }
}
