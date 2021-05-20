<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditRegularExpressionCommand extends AbstractEditAttributeCommand
{
    public ?string $regularExpression = null;

    public function __construct(string $identifier, ?string $regularExpression)
    {
        parent::__construct($identifier);

        $this->regularExpression = $regularExpression;
    }
}
