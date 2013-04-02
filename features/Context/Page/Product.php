<?php

namespace Context\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class Product extends Page
{
    protected $path = '/{locale}/product/{id}/edit';
}

