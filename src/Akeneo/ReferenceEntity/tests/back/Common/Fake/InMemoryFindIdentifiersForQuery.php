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
class InMemoryFindIdentifiersForQuery implements FindIdentifiersForQueryInterface
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
        $search = $query->getFilter('search')['value'];

        $identifiers = array_values(array_filter($this->identifiers, function (string $identifier) use ($search) {
            return '' === $search || false !== strpos($identifier, $search);
        }));

        $result = new IdentifiersForQueryResult();
        $result->total = count($identifiers);
        $result->identifiers = $identifiers;

        return $result;
    }
}
