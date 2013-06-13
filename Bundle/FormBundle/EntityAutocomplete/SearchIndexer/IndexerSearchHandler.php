<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete\SearchIndexer;

use Oro\Bundle\SearchBundle\Formatter\ResultFormatter;

use Oro\Bundle\FormBundle\EntityAutocomplete\SearchHandlerInterface;
use Oro\Bundle\SearchBundle\Engine\Indexer;

class IndexerSearchHandler implements SearchHandlerInterface
{
    /**
     * @var Indexer
     */
    protected $indexer;

    /**
     * @var ResultFormatter
     */
    protected $resultFormatter;

    /**
     * @var string
     */
    protected $entityAlias;

    /**
     * @param Indexer $indexer
     * @param ResultFormatter $resultFormatter
     * @param $entityAlias
     * @throws \LogicException
     */
    public function __construct(Indexer $indexer, ResultFormatter $resultFormatter, $entityAlias)
    {
        $this->indexer         = $indexer;
        $this->resultFormatter = $resultFormatter;
        $this->entityAlias     = $entityAlias;
    }

    /**
     * {@inheritdoc}
     */
    public function search($search, $firstResult, $maxResults)
    {
        $result = $this->indexer->simpleSearch($search, $firstResult, $maxResults, $this->entityAlias);
        $elements = $result->getElements();

        return $this->resultFormatter->getOrderedResultEntities($elements);
    }
}
