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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\EmptyData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\RecordCollectionData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueDataInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordExistsInterface;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordCollectionDataHydrator implements DataHydratorInterface
{
    /** @var RecordExistsInterface */
    private $recordExists;

    /**
     * Maybe inject a Bulk Record Exists here
     */
    public function __construct(RecordExistsInterface $recordExists)
    {
        $this->recordExists = $recordExists;
    }

    public function supports(AbstractAttribute $attribute): bool
    {
        return $attribute instanceof RecordCollectionAttribute;
    }

    public function hydrate($normalizedData, AbstractAttribute $attribute): ValueDataInterface
    {
        $filteredRecords = $this->keepExistingRecordsOnly($normalizedData, $attribute);
        if (empty($filteredRecords)) {
            return EmptyData::create();
        }

        return RecordCollectionData::createFromNormalize($filteredRecords);
    }

    private function keepExistingRecordsOnly(
        array $recordCodesFromDatabase,
        RecordCollectionAttribute $recordCollectionAttribute
    ): array {
        return array_filter(
            $recordCodesFromDatabase,
            function (string $recordCodeFromDatabase) use ($recordCollectionAttribute) {
                return $this->recordExists->withReferenceEntityAndCode(
                    $recordCollectionAttribute->getRecordType(),
                    RecordCode::fromString($recordCodeFromDatabase)
                );
            }
        );
    }
}
