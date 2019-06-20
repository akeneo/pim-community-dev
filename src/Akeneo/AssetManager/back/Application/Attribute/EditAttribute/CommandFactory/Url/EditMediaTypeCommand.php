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

namespace Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\Url;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\AbstractEditAttributeCommand;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class EditMediaTypeCommand extends AbstractEditAttributeCommand
{
    /** @var string */
    public $mediaType;

    public function __construct(string $identifier, string $mediaType)
    {
        parent::__construct($identifier);

        $this->mediaType = $mediaType;
    }
}
