<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordItem;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordLabelsByCodesInterface;

/**
 * RecordItem Value hydrator for value of type "Record" & "Record Collection".
 * It retrieves the labels of linked records and add them into a value context for the frontend.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class RecordValueHydrator implements ValueHydratorInterface
{
    /** @var FindRecordLabelsByCodesInterface */
    private $findRecordLabelsByCodes;

    public function __construct(FindRecordLabelsByCodesInterface $findRecordLabelsByCodes)
    {
        $this->findRecordLabelsByCodes = $findRecordLabelsByCodes;
    }

    public function supports(AbstractAttribute $attribute): bool
    {
        return $attribute instanceof RecordAttribute || $attribute instanceof RecordCollectionAttribute;
    }

    public function hydrate($normalizedValue, AbstractAttribute $attribute, array $context = []): array
    {
        $recordIdentifiers = is_array($normalizedValue['data']) ? $normalizedValue['data'] : [$normalizedValue['data']];
        $data = array_values(array_intersect(array_keys($context['labels']), $recordIdentifiers));
        $labels = array_intersect_key($context['labels'], array_flip($recordIdentifiers));
        $normalizedValue['data'] = ('record' === $attribute->getType()) ? $data[0] : $data;
        $normalizedValue['context']['labels'] = $labels;

        return $normalizedValue;
    }
}
