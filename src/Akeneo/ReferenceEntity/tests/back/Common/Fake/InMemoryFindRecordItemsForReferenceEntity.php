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
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordItemsForReferenceEntityInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordItem;

/**
 * @author Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindRecordItemsForReferenceEntity implements FindRecordItemsForReferenceEntityInterface
{
    /** @var RecordItem[] */
    private $results = [];

    public function save(RecordItem $recordItem)
    {
        $this->results[(string) $recordItem->referenceEntityIdentifier][] = $recordItem;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ReferenceEntityIdentifier $identifier): array
    {
        return $this->results[(string) $identifier] ?? [];
    }
}
