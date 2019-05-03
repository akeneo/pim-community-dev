<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class EditIsDecimalCommand extends AbstractEditAttributeCommand
{
    /** @var bool|null */
    public $isDecimal;

    public function __construct(string $identifier, ?bool $isDecimal)
    {
        parent::__construct($identifier);

        $this->isDecimal = $isDecimal;
    }
}
