<?php

namespace Response\Bundle\BigQueryProductImportBundle\Reader\BigQuery;

use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorInterface;
use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\Core\Iterator\ItemIterator;

class BigQueryIterator implements FileIteratorInterface
{

    /** @var string **/
    protected $type;

    /** @var string **/
    protected $filePath;

    /** @var ItemIterator */
    protected $bigQueryIterator;

    /**
     * {@inheritdoc}
     */
    public function __construct($type, $filePath, array $options = [])
    {
        $this->type     = $type;
        $this->filePath = $filePath;
        $bigQueryDataset = $options['bigquery_dataset'];

        // Construct and execute a query to retrieve the products to
        // import from BigQuery
        $bigQuery = new BigQueryClient();
        $queryJobConfig = $bigQuery->query(
            "SELECT * FROM `{$bigQueryDataset}`"
        );
        $queryResults = $bigQuery->runQuery($queryJobConfig);
        $this->bigQueryIterator = $queryResults->getIterator();
        $this->bigQueryIterator->rewind();
    }

    /**
     * {@inheritdoc}
     */
    public function getDirectoryPath()
    {
        return $this->filePath;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        $headers = [];
        foreach ($this->bigQueryIterator->current() as $column => $value) {
            $headers[] = $column;
        }

        return $headers;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $elem = $this->bigQueryIterator->current();

        return $this->elementToFlat($elem);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->bigQueryIterator->next();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->bigQueryIterator->key();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->bigQueryIterator->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->bigQueryIterator->rewind();
    }

    protected function elementToFlat($elem)
    {
        return $elem;
    }
}
