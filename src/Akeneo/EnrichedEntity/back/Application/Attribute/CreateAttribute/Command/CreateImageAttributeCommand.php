<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\Command;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CreateImageAttributeCommand extends AbstractCreateAttributeCommand
{
    /** @var float */
    public $maxFileSize;

    /** @var array */
    public $extensions;
}
