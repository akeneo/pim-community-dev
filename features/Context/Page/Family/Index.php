<?php

namespace Context\Page\Family;

use Context\Page\Base\Index as BaseIndex;

/**
 * Family index page
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index extends BaseIndex
{
    protected $path = '/enrich/family/create';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = array())
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            array(
                'List' => array('css' => '.sidebar-list ul'),
            )
        );
    }

    /**
     * @return array
     */
    public function getFamilies()
    {
        return array_map(
            function ($node) {
                return $node->getText();
            },
            $this->getElement('List')->findAll('css', 'a:not(.btn)')
        );
    }

    /**
     * @param string $family
     *
     * @return NodeElement
     */
    public function getFamilyLink($family)
    {
        return $this->getElement('List')->find('css', sprintf('a:contains("%s")', $family));
    }
}
