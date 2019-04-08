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
use Akeneo\ReferenceEntity\Domain\Query\Record\FindCodesByIdentifiersInterface;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordCollectionDataHydrator implements DataHydratorInterface
{
    /** @var FindCodesByIdentifiersInterface */
    private $findCodesByIdentifiers;

    public function __construct(FindCodesByIdentifiersInterface $findCodesByIdentifiers)
    {
        $this->findCodesByIdentifiers = $findCodesByIdentifiers;
    }

    public function supports(AbstractAttribute $attribute): bool
    {
        return $attribute instanceof RecordCollectionAttribute;
    }

    public function hydrate($normalizedData, AbstractAttribute $attribute): ValueDataInterface
    {
        $filteredRecordCodes = $this->findCodes($normalizedData);
        if (empty($filteredRecordCodes)) {
            return EmptyData::create();
        }

        return RecordCollectionData::createFromNormalize($filteredRecordCodes);
    }

    /**
     * This method also cleans missing records (returns only existing ones)
     */
    private function findCodes(array $identifiers): array
    {
        return $this->findCodesByIdentifiers->find($identifiers);
    }
}
