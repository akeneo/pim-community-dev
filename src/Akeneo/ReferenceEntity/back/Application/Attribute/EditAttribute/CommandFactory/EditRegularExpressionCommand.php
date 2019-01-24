<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditRegularExpressionCommand extends AbstractEditAttributeCommand
{
    /** @var string */
    public $regularExpression;

    public function __construct(string $identifier, ?string $regularExpression)
    {
        parent::__construct($identifier);

        $this->regularExpression = $regularExpression;
    }
}
