<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete;

class SearchRegistry
{
    /**
     * @var SearchHandler
     */
    protected $searchHandlers = array();

    /**
     * @param string $name
     * @param SearchHandlerInterface $searchHandler
     */
    public function addSearchHandler($name, SearchHandlerInterface $searchHandler)
    {
        $this->searchHandlers[$name] = $searchHandler;
    }

    /**
     * @param string $name
     * @return SearchHandlerInterface
     * @throws \RuntimeException
     */
    public function getSearchHandler($name)
    {
        if (!isset($this->searchHandlers[$name])) {
            throw new \RuntimeException(sprintf('Search handler "%s" is not registered', $name));
        }

        return $this->searchHandlers[$name];
    }
}
