<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Application\Record;

use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditRecordCommand;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class CreateAndEditRecordCommand
{
    public ?CreateRecordCommand $createRecordCommand;
    public EditRecordCommand $editRecordCommand;

    public function __construct(?CreateRecordCommand $createRecordCommand, EditRecordCommand $editRecordCommand)
    {
        $this->createRecordCommand = $createRecordCommand;
        $this->editRecordCommand = $editRecordCommand;
    }
}
