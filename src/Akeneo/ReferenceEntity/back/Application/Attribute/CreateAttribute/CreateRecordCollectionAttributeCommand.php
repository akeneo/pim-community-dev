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
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class CreateRecordCollectionAttributeCommand extends AbstractCreateAttributeCommand
{
    public function __construct(
        string $referenceEntityIdentifier,
        string $code,
        array $labels,
        bool $isRequired,
        bool $valuePerChannel,
        bool $valuePerLocale,
        public ?string $recordType
    ) {
        parent::__construct(
            $referenceEntityIdentifier,
            $code,
            $labels,
            $isRequired,
            $valuePerChannel,
            $valuePerLocale
        );
    }
}
