<?php

namespace Context\Page\Family;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index extends Page
{
    protected $path = '/enrich/family/';

    protected $elements = array(
        'List' => array('css' => '.sidebar-list ul'),
    );

    public function getFamilies()
    {
        return array_map(function ($node) {
            return $node->getText();
        }, $this->getElement('List')->findAll('css', 'a:not(.btn)'));
    }

    public function getFamilyLink($family)
    {
        return $this
            ->getElement('List')
            ->find('css', sprintf('a:contains("%s")', $family))
        ;
    }
}
