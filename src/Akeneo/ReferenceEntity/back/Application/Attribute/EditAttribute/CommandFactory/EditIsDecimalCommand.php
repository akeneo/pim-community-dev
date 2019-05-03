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

namespace Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
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
