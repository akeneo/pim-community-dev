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

use Akeneo\EnrichedEntity\Domain\Model\Attribute\ImageAttribute;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditFileValueCommand extends AbstractEditValueCommand
{
    /** @var ImageAttribute */
    public $attribute;

    /** @var string */
    public $filePath;

    /** @var string */
    public $originalFilename;
}
