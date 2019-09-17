<?php

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;

/**
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CursorFactory implements CursorFactoryInterface
{
    /** @var Client */
    protected $searchEngine;

    /** @var CursorableRepositoryInterface */
    private $productRepository;

    /** @var CursorableRepositoryInterface */
    private $productModelRepository;

    /** @var string */
    protected $cursorClassName;

    /** @var int */
    protected $pageSize;

    /**
     * @param Client                        $searchEngine
     * @param CursorableRepositoryInterface $productRepository
     * @param CursorableRepositoryInterface $productModelRepository
     * @param int                           $pageSize
     */
    public function __construct(
        Client $searchEngine,
        CursorableRepositoryInterface $productRepository,
        CursorableRepositoryInterface $productModelRepository,
        int $pageSize
    ) {
        $this->searchEngine = $searchEngine;
        $this->productRepository = $productRepository;
        $this->productModelRepository = $productModelRepository;
        $this->pageSize = $pageSize;
    }

    /**
     * {@inheritdoc}
     */
    public function createCursor($queryBuilder, array $options = [])
    {
        $pageSize = !isset($options['page_size']) ? $this->pageSize : $options['page_size'];

        $queryBuilder['_source'] = array_merge($queryBuilder['_source'], ['document_type']);

        return new Cursor(
            $this->searchEngine,
            $this->productRepository,
            $this->productModelRepository,
            $queryBuilder,
            $pageSize
        );
    }
}
