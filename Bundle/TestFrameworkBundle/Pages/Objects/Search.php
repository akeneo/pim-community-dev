<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\Page;

class Search extends Page
{
    protected $simpleSearch;
    protected $searchButton;

    public function __construct($testCase)
    {
        parent::__construct($testCase);
        $this->simpleSearch = $this->byId('search-bar-search');
        $this->searchButton = $this->byXPath("//form[@id='top-search-form']//div/button[contains(.,'Search')]");
    }

    public function search($value)
    {
        $this->simpleSearch->clear();
        $this->simpleSearch->value($value);
        $this->waitForAjax();
        return $this;
    }

    public function suggestions($filter = null)
    {
        if (!is_null($filter)) {
            $result = $this->elements($this->using("xpath")->value("//div[@id='search-dropdown']/ul/li/a[contains(., '{$filter}')]"));
        } else {
            $result = $this->elements($this->using("xpath")->value("//div[@id='search-dropdown']/ul/li/a"));
        }

        return $result;
    }

    public function result($filter)
    {
        if (!is_null($filter)) {
            $result = $this->elements($this->using("xpath")->value("//div[@class= 'container-fluid']/div/h3/a[contains(., '{$filter}')]"));
        } else {
            $result = $this->elements($this->using("xpath")->value("//div[@class= 'container-fluid']/div/h3/a"));
        }

        return $result;
    }

    public function submit()
    {
        $this->searchButton->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return $this;
    }
}
