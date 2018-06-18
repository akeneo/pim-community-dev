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

namespace Akeneo\EnrichedEntity\back\Domain\Query;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\back\Domain\Model\Record\RecordIdentifier;

/**
 * Read model representing a record within the list.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordItem
{
    public const IDENTIFIER = 'identifier';

    public const ENRICHED_ENTITY_IDENTIFIER = 'enriched_entity_identifier';

    public const LABELS = 'labels';

    /** @var RecordIdentifier */
    public $identifier;

    /** @var EnrichedEntityIdentifier */
    public $enrichedEntityIdentifier;

    /** @var LabelCollection */
    public $labels;

    public function normalize(): array
    {
        return [
            self::IDENTIFIER                 => (string) $this->identifier,
            self::ENRICHED_ENTITY_IDENTIFIER => (string) $this->enrichedEntityIdentifier,
            self::LABELS                     => $this->normalizeLabels($this->labels)
        ];
    }

    private function normalizeLabels(LabelCollection $labelCollection): array
    {
        $labels = [];
        foreach ($this->labels->getLocaleCodes() as $localeCode) {
            $labels[$localeCode] = $labelCollection->getLabel($localeCode);
        }

        return $labels;
    }
}
