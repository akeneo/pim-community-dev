<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;

/**
 * Command to edit the options of an "option collection" attribute.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditOptionCollectionValueCommand extends AbstractEditValueCommand
{
    /** @var string[] */
    public $optionCodes;

    public function __construct(OptionCollectionAttribute $attribute, ?string $channel, ?string $locale, array $optionCodes)
    {
        parent::__construct($attribute, $channel, $locale);

        $this->optionCodes = $optionCodes;
    }
}
