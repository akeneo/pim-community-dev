<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditOptionsCommand extends AbstractEditAttributeCommand
{
    /** @var array */
    public $options;

    public function __construct(string $identifier, array $options)
    {
        parent::__construct($identifier);

        $this->options = $options;
    }
}
