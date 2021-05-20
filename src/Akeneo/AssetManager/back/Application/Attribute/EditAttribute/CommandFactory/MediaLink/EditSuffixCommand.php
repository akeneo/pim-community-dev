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

namespace Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\MediaLink;

use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\AbstractEditAttributeCommand;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class EditSuffixCommand extends AbstractEditAttributeCommand
{
    public ?string $suffix = null;

    public function __construct(string $identifier, ?string $suffix)
    {
        parent::__construct($identifier);

        $this->suffix = $suffix;
    }
}
