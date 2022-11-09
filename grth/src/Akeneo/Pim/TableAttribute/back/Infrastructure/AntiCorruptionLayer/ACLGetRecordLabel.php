<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\AntiCorruptionLayer;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ReferenceEntityIdentifier;
use Akeneo\Pim\TableAttribute\Domain\Value\Query\GetRecordLabel;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier as ForeignReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordLabelsByCodesInterface;
use Webmozart\Assert\Assert;

class ACLGetRecordLabel implements GetRecordLabel
{
    public function __construct(private ?FindRecordLabelsByCodesInterface $findRecordLabelsByCodes)
    {
    }

    public function __invoke(ReferenceEntityIdentifier $referenceEntityIdentifier, string $recordCode, string $localeCode): ?string
    {
        Assert::notNull($this->findRecordLabelsByCodes);

        try {
            $foreignReferenceEntityIdentifier = ForeignReferenceEntityIdentifier::fromString($referenceEntityIdentifier->asString());
            $foreignRecordCode = RecordCode::fromString($recordCode);
        } catch (\InvalidArgumentException) {
            return null;
        }
        $labels = $this->findRecordLabelsByCodes->find($foreignReferenceEntityIdentifier, [$foreignRecordCode]);

        return isset($labels[$recordCode]) ? $labels[$recordCode]->getLabel($localeCode) : null;
    }
}
