<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @api
 */
class AbstractEditAttributeCommand
{
    public string $identifier;

    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }
}
