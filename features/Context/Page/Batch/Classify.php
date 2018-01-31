<?php

namespace Context\Page\Batch;

use Context\Page\Base\Wizard;

/**
 * Batch Classify page
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Classify extends Wizard
{
    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            [
                'Trees list'    => ['css' => '.nav.nav-tabs'],
                'Category tree' => [
                    'css'        => '#trees',
                    'decorators' => [
                        'Pim\Behat\Decorator\Tree\JsTreeDecorator'
                    ]
                ],
            ]
        );
    }

    /**
     * @param string $category
     *
     * @return CategoryView
     */
    public function selectTree($category)
    {
        $link = $this->getElement('Trees list')
            ->find('css', sprintf('li span:contains("%s")', $category));
        if (!$link) {
            throw new \InvalidArgumentException(sprintf('Tree "%s" not found', $category));
        }
        $link->click();

        return $this;
    }
}
