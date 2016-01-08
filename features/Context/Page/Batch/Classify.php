<?php

namespace Context\Page\Batch;

use Context\Page\Base\Wizard;
use Pim\Behat\Manipulator\TreeManipulator\JsTreeManipulator;

/**
 * Batch Classify page
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Classify extends Wizard
{
    /** @var JsTreeManipulator */
    protected $jsTreeManipulator;

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->jsTreeManipulator = new JsTreeManipulator();
        $this->elements = array_merge(
            $this->elements,
            [
                'Trees list'    => ['css' => '#trees-list'],
                'Category tree' => ['css' => '#trees'],
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
            ->find('css', sprintf('#trees-list li a:contains("%s")', $category));
        if (!$link) {
            throw new \InvalidArgumentException(sprintf('Tree "%s" not found', $category));
        }
        $link->click();

        return $this;
    }

    /**
     * @param string $category
     *
     * @return Classify
     */
    public function expandCategory($category)
    {
        $this->jsTreeManipulator->expandNode($this->getElement('Category tree'), $category);

        return $this;
    }

    /**
     * @param string $category
     *
     * @throws \InvalidArgumentException
     *
     * @return NodeElement
     */
    public function findCategoryInTree($category)
    {
        return $this->jsTreeManipulator->findNodeInTree($this->getElement('Category tree'), $category);
    }
}
