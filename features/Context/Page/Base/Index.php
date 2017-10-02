<?php

namespace Context\Page\Base;

/**
 * Basic index page
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index extends Base
{
    /**
     * @var string
     */
    protected $path = '#/';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = array())
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            array(
                'Creation link' => array('css' => '.AknTitleContainer .AknButton--apply'),
            )
        );
    }

    /**
     * Click the create button
     */
    public function clickCreationLink()
    {
        $this->getElement('Creation link')->click();
    }
}
