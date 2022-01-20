<?php

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\Cursor;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;

/**
 * Cursor factory to instantiate an elasticsearch cursor
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierCursorFactory implements CursorFactoryInterface
{
    public function __construct(
        private Client $searchEngine,
        private string $cursorClassName,
        private int $pageSize
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function createCursor($queryBuilder, array $options = [])
    {
        $pageSize = $options['page_size'] ?? $this->pageSize;
        if (!isset($queryBuilder['_source'])) {
            $queryBuilder['_source'] = [];
        }
        foreach (['identifier', 'document_type'] as $sourceItem) {
            if (!\in_array($sourceItem,  $queryBuilder['_source'])) {
                $queryBuilder['_source'][] = $sourceItem;
            }
        }

        return new $this->cursorClassName($this->searchEngine, $queryBuilder, $pageSize);
    }
}
