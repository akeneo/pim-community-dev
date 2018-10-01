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

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordDetailsInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordDetails;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindRecordDetails implements FindRecordDetailsInterface
{
    /** @var RecordDetails[] */
    private $results;

    public function __construct()
    {
        $this->results = [];
    }

    public function save(RecordDetails $recordDetails)
    {
        $normalized = $recordDetails->normalize();
        $referenceEntityIdentifier = $normalized['reference_entity_identifier'];
        $code = $normalized['code'];

        $this->results[sprintf('%s____%s', $referenceEntityIdentifier, $code)] = $recordDetails;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        RecordCode $recordCode
    ): ?RecordDetails {
        return $this->results[sprintf('%s____%s', $referenceEntityIdentifier, $recordCode)] ?? null;
    }
}
