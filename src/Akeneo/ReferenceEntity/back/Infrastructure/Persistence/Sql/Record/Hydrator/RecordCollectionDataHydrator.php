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
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\EmptyData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\RecordCollectionData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueDataInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\SqlRecordsExists;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordCollectionDataHydrator implements DataHydratorInterface
{
    /** @var SqlRecordsExists */
    private $recordsExists;

    public function __construct(SqlRecordsExists $recordsExists)
    {
        $this->recordsExists = $recordsExists;
    }

    public function supports(AbstractAttribute $attribute): bool
    {
        return $attribute instanceof RecordCollectionAttribute;
    }

    public function hydrate($normalizedData, AbstractAttribute $attribute): ValueDataInterface
    {
        $filteredRecords = $this->keepExistingRecordsOnly($normalizedData);
        if (empty($filteredRecords)) {
            return EmptyData::create();
        }

        return RecordCollectionData::createFromNormalize($filteredRecords);
    }

    private function keepExistingRecordsOnly(array $recordIdentifiers): array
    {
        return $this->recordsExists->withIdentifiers($recordIdentifiers);
    }
}
