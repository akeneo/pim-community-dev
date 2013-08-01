<?php

namespace Context\Page\Base;

use Behat\Mink\Exception\ElementNotFoundException;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Form extends Base
{
    public function __construct($session, $pageFactory, $parameters = array())
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            array(
                'Tabs' => array('css' => '#form-navbar'),
            )
        );
    }

    public function save()
    {
        $this->pressButton('Save');
    }

    public function visitTab($tab)
    {
        $this->getElement('Tabs')->clickLink($tab);
    }

    public function getSection($title)
    {
        return $this->find('css', sprintf('div.accordion-heading:contains("%s")', $title));
    }
}
