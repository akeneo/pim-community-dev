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

use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditStoredFileValueCommand extends AbstractEditValueCommand
{
    /** @var ImageAttribute */
    public $attribute;

    /** @var string */
    public $filePath;

    /** @var string */
    public $originalFilename;

    /** @var int */
    public $size;

    /** @var string */
    public $mimeType;

    /** @var string */
    public $extension;
}
