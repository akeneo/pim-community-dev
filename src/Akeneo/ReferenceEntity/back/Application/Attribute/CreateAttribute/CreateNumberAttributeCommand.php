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

namespace Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class CreateNumberAttributeCommand extends AbstractCreateAttributeCommand
{
    /** @var bool */
    public $isDecimal;

    public function __construct(
        string $referenceEntityIdentifier,
        string $code,
        array $labels,
        bool $isRequired,
        bool $valuePerChannel,
        bool $valuePerLocale,
        bool $isDecimal
    ) {
        parent::__construct(
            $referenceEntityIdentifier,
            $code,
            $labels,
            $isRequired,
            $valuePerChannel,
            $valuePerLocale
        );

        $this->isDecimal = $isDecimal;
    }
}
