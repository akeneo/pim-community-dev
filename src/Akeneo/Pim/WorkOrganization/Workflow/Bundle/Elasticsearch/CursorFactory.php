<?php

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;

/**
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class CursorFactory implements CursorFactoryInterface
{
    /** @var Client */
    protected $searchEngine;

    /** @var CursorableRepositoryInterface */
    private $productDraftRepository;

    /** @var CursorableRepositoryInterface */
    private $productModelDraftRepository;

    /** @var string */
    protected $cursorClassName;

    /** @var int */
    protected $pageSize;

    /** @var string */
    protected $indexType;

    /**
     * @param Client                        $searchEngine
     * @param CursorableRepositoryInterface $productDraftRepository
     * @param CursorableRepositoryInterface $productModelDraftRepository
     * @param int                           $pageSize
     * @param string                        $indexType
     */
    public function __construct(
        Client $searchEngine,
        CursorableRepositoryInterface $productDraftRepository,
        CursorableRepositoryInterface $productModelDraftRepository,
        int $pageSize,
        string $indexType
    ) {
        $this->searchEngine = $searchEngine;
        $this->productDraftRepository = $productDraftRepository;
        $this->productModelDraftRepository = $productModelDraftRepository;
        $this->pageSize = $pageSize;
        $this->indexType = $indexType;
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
            $this->productDraftRepository,
            $this->productModelDraftRepository,
            $queryBuilder,
            $this->indexType,
            $pageSize
        );
    }
}
