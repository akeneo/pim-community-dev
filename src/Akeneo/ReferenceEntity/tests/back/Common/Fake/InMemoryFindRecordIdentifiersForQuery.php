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

use Akeneo\ReferenceEntity\Domain\Query\Record\FindIdentifiersForQueryInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\IdentifiersForQueryResult;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;

/**
 * @author    Julien Sanchez <julienakeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindRecordIdentifiersForQuery implements FindIdentifiersForQueryInterface
{
    /** @var string[] */
    private $identifiers = [];

    public function add(string $identifier): void
    {
        $this->identifiers[$identifier] = $identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(RecordQuery $query): IdentifiersForQueryResult
    {
        $referenceEntityCode = $query->getFilter('reference_entity');
        $fullTextFilter = ($query->hasFilter('full_text')) ? $query->getFilter('full_text') : null;
        $codeFilter = ($query->hasFilter('code')) ? $query->getFilter('code') : null;

        $identifiers = array_values(array_filter($this->identifiers, function (string $identifier) use ($referenceEntityCode) {
            return '' === $referenceEntityCode['value'] ||
                false !== strpos($identifier, $referenceEntityCode['value']);
        }));

        $identifiers = array_values(array_filter($identifiers, function (string $identifier) use ($fullTextFilter) {
            return null === $fullTextFilter ||
                '' === $fullTextFilter['value'] ||
                false !== strpos($identifier, $fullTextFilter['value']);
        }));

        $identifiers = array_values(array_filter($identifiers, function (string $identifier) use ($codeFilter) {
            return null === $codeFilter ||
                false === strpos($identifier, $codeFilter['value']);
        }));

        $result = new IdentifiersForQueryResult();
        $result->total = count($identifiers);
        $result->identifiers = $identifiers;

        return $result;
    }
}
