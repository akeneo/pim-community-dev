<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Elasticsearch\Sorter\Field;

use Pim\Bundle\CatalogBundle\Elasticsearch\Sorter\Field\BaseFieldSorter;
use Pim\Component\Catalog\Exception\InvalidDirectionException;
use Pim\Component\Catalog\Query\Sorter\Directions;

/**
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class AuthorSorter extends BaseFieldSorter
{
    const AUTHOR_KEY = 'author';

    public function addFieldSorter($field, $direction, $locale = null, $channel = null)
    {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the sorter.');
        }

        switch ($direction) {
            case Directions::ASCENDING:
                $sortAuthorClause = [
                    self::AUTHOR_KEY => [
                        'order'         => 'ASC',
                        'unmapped_type' => 'string',
                        'missing'       => '_last',
                    ],
                ];
                $this->searchQueryBuilder->addSort($sortAuthorClause);

                break;
            case Directions::DESCENDING:
                $sortAuthorClause = [
                    self::AUTHOR_KEY => [
                        'order'         => 'DESC',
                        'unmapped_type' => 'string',
                        'missing'       => '_last',
                    ],
                ];
                $this->searchQueryBuilder->addSort($sortAuthorClause);

                break;
            default:
                throw InvalidDirectionException::notSupported($direction, static::class);
        }
    }
}
