<?php

namespace Context\Page\Category;

/**
 * Category tree edit page
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends CategoryView
{
    /**
     * @var string
     */
    protected $path = '/enrich/category-tree/{id}/edit';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = array())
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            array(
                'Updates grid' => array('css' => '#history table.grid'),
            )
        );
    }

    /**
     * @return array
     */
    public function getHistoryRows()
    {
        return $this->getElement('Updates grid')->findAll('css', 'tbody tr');
    }
}
