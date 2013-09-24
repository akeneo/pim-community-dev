<?php

namespace Oro\Bundle\GridBundle\Datagrid\Views;

use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractViewsList
{
    const PARAM_KEY = 'view';

    /** @var TranslatorInterface */
    protected $translator;

    /** @var null|ArrayCollection */
    protected $views = null;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Returns an array of available views
     *
     * @return View[]
     */
    abstract protected function getViewsList();

    /**
     * Public interface to retrieve list
     *
     * @return ArrayCollection
     */
    public function getList()
    {
        if (!$this->views instanceof ArrayCollection) {
            $list = $this->getViewsList();
            $this->validate($list);

            $this->views = new ArrayCollection($list);
        }

        return $this->views;
    }

    /**
     * Find and returns view object by name
     *
     * @param string $name
     *
     * @return View|bool
     */
    public function getViewByName($name)
    {
        if (empty($name)) {
            return false;
        }

        $filtered = $this->getList()->filter(
            function (View $view) use ($name) {
                return $view->getName() === $name;
            }
        );

        return $filtered->first();
    }

    /**
     * Returns array of choices for choice widget
     *
     * @return array
     */
    public function toChoiceList()
    {
        $choices = array();

        /** @var View $view */
        foreach ($this->getList() as $view) {
            $choices[] = array('value' => $view->getName(), 'label' => $this->translator->trans($view->getName()));
        }

        return $choices;
    }

    public function toViewData()
    {
        $result = $this->getList()->map(
            function (View $view) {
                return $view->toViewData();
            }
        );

        return array(
            'choices' => $this->toChoiceList(),
            'views'   => $result->toArray()
        );
    }

    /**
     * Validates input array
     *
     * @param array $list
     *
     * @throws \InvalidArgumentException
     */
    protected function validate(array $list)
    {
        foreach ($list as $view) {
            if (!$view instanceof View) {
                throw new \InvalidArgumentException('List should contains only instances of View class');
            }
        }
    }
}
