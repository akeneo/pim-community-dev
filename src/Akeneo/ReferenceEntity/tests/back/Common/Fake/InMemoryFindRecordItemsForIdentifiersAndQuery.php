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
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordItemsForIdentifiersAndQueryInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordItemHydrator;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindRecordItemsForIdentifiersAndQuery implements FindRecordItemsForIdentifiersAndQueryInterface
{
    /** @var RecordRepositoryInterface */
    private $recordRepository;

    /** @var ReferenceEntityRepositoryInterface  */
    private $referenceEntityRepository;

    /** @var RecordItemHydrator */
    private $recordItemHydrator;

    public function __construct(
        RecordRepositoryInterface $recordRepository,
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        RecordItemHydrator $recordItemHydrator
    ) {
        $this->recordRepository = $recordRepository;
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->recordItemHydrator = $recordItemHydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(array $identifiers, RecordQuery $query): array
    {
        $recordItems = [];
        foreach ($identifiers as $identifier) {
            $normalizedRecord = $this->recordRepository->getByIdentifier(RecordIdentifier::fromString($identifier))->normalize();
            $referenceEntity = $this->referenceEntityRepository->getByIdentifier(
                ReferenceEntityIdentifier::fromString($normalizedRecord['referenceEntityIdentifier'])
            );

            $recordItems[] = $this->recordItemHydrator->hydrate([
                'identifier' => $normalizedRecord['identifier'],
                'code' => $normalizedRecord['code'],
                'reference_entity_identifier' => $normalizedRecord['referenceEntityIdentifier'],
                'value_collection' => json_encode($normalizedRecord['values']),
                'attribute_as_label' => $referenceEntity->getAttributeAsLabelReference()->normalize(),
                'attribute_as_image' => $referenceEntity->getAttributeAsImageReference()->normalize(),
            ], $query);
        }

        return $recordItems;
    }
}
