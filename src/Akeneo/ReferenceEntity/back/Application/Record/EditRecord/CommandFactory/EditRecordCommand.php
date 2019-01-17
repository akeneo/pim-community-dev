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

/**
 * It represents the intent to edit a record
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditRecordCommand
{
    /** @var string */
    public $referenceEntityIdentifier;

    /** @var string */
    public $code;

    /** @var array */
    public $labels = [];

    /** @var string[]|null */
    public $image;

    /** @var array */
    public $editRecordValueCommands = [];
}
