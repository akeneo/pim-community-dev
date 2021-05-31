<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditValidationRuleCommand extends AbstractEditAttributeCommand
{
    public ?string $validationRule = null;

    public function __construct(string $identifier, ?string $validationRule)
    {
        parent::__construct($identifier);

        $this->validationRule = $validationRule;
    }
}
