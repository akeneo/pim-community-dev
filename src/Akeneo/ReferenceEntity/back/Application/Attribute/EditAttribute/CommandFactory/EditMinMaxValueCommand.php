<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditMinMaxValueCommand extends AbstractEditAttributeCommand
{
    /** @var string|null */
    public $minValue;

    /** @var string|null */
    public $maxValue;

    public function __construct(string $identifier, ?string $minValue, ?string $maxValue)
    {
        parent::__construct($identifier);

        $this->minValue = $minValue;
        $this->maxValue = $maxValue;
    }
}
