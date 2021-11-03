<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Query\Record;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SearchableRecordItem
{
    public string $identifier;
    public string $referenceEntityIdentifier;
    public string $code;
    public array $labels;
    public array $values;
    public \DateTimeImmutable $updatedAt;
}
