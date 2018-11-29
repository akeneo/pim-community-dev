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

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordItemsForIdentifiersAndQueryInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordItem;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Domain\Repository\RecordNotFoundException;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindRecordItemsForIdentifiersAndQuery implements FindRecordItemsForIdentifiersAndQueryInterface
{
    /** @var RecordRepositoryInterface */
    private $recordRepository;

    public function __construct(RecordRepositoryInterface $recordRepository)
    {
        $this->recordRepository = $recordRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(array $identifiers, RecordQuery $query): array
    {
        return array_values(array_filter(array_map(function (string $identifier) {
            try {
                $record = $this->recordRepository->getByIdentifier(RecordIdentifier::fromString($identifier));
            } catch (RecordNotFoundException $exception) {
                return false;
            }

            $recordItem = new RecordItem();
            $recordItem->identifier = (string) $record->getIdentifier();
            $recordItem->referenceEntityIdentifier = (string) $record->getReferenceEntityIdentifier();
            $recordItem->code = (string) $record->getCode();
            $recordItem->labels = $record->normalize()['labels'];
            $recordItem->image = $record->getImage()->normalize();
            $recordItem->values = $record->getValues()->normalize();
            $recordItem->completenessPercentage = '-';

            return $recordItem;
        }, $identifiers)));
    }
}
