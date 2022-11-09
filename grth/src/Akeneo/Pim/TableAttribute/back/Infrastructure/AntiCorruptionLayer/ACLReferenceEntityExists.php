<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\AntiCorruptionLayer;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\ReferenceEntityExists;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Webmozart\Assert\Assert;

final class ACLReferenceEntityExists implements ReferenceEntityExists
{
    public function __construct(private ?ReferenceEntityExistsInterface $referenceEntityExists)
    {
    }

    public function forIdentifier(string $identifier): bool
    {
        Assert::notNull($this->referenceEntityExists);
        try {
            $identifier = ReferenceEntityIdentifier::fromString($identifier);
        } catch (\InvalidArgumentException) {
            return false;
        }

        return $this->referenceEntityExists->withIdentifier($identifier);
    }
}
