<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete\SearchIndexer;

use Oro\Bundle\SearchBundle\Provider\ResultProvider;

use Oro\Bundle\FormBundle\EntityAutocomplete\SearchHandlerInterface;
use Oro\Bundle\SearchBundle\Engine\Indexer;

class IndexerSearchHandler implements SearchHandlerInterface
{
    /**
     * @var Indexer
     */
    protected $indexer;

    /**
     * @var ResultProvider
     */
    protected $resultProvider;

    /**
     * @var string
     */
    protected $entityAlias;

    /**
     * @param Indexer $indexer
     * @param ResultProvider $resultProvider
     * @param $entityAlias
     * @throws \LogicException
     */
    public function __construct(Indexer $indexer, ResultProvider $resultProvider, $entityAlias)
    {
        $this->indexer        = $indexer;
        $this->resultProvider = $resultProvider;
        $this->entityAlias    = $entityAlias;
    }

    /**
     * {@inheritdoc}
     */
    public function search($search, $firstResult, $maxResults)
    {
        $result = $this->indexer->simpleSearch($search, $firstResult, $maxResults, $this->entityAlias);
        $elements = $result->getElements();

        return $this->resultProvider->getOrderedResultEntities($elements);
    }
}
