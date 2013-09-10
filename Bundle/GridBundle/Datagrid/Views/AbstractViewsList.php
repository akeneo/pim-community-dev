<?php

namespace Oro\Bundle\GridBundle\Datagrid\Views;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\GridBundle\Datagrid\Datagrid;

abstract class AbstractViewsList
{
    /** @var TranslatorInterface */
    protected $translator;

    /** @var null|ArrayCollection */
    protected $views = null;

    public function __construct(TranslatorInterface $tranlator)
    {
        $this->translator = $tranlator;
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
            $choices[$view->getName()] = $this->translator->trans($view->getName());
        }

        return $choices;
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

    /**
     * Apply view to datagrid
     *
     * @param Datagrid $datagrid
     * @param array $defaultGridParameters assoc array with datagrid default params
     * @return bool
     */
    public function applyToDatagrid(Datagrid $datagrid, $defaultGridParameters)
    {
        $parameters = $datagrid->getParameters();

        $filters = $parameters->get(ParametersInterface::FILTER_PARAMETERS);
        $sorters = $parameters->get(ParametersInterface::SORT_PARAMETERS);
        $viewName = $parameters->get(ParametersInterface::ADDITIONAL_PARAMETERS);

        // test
        $viewName = 'testGroupView';

        // find view by name
        $view = $this->getViewByName($viewName);
        if (!$view) {
            return false;
        }

        $parameters->set('_filters', $view->getFiltersData());
        $viewSorters = $view->getSortersData();
        if (empty($viewSorters)) {
            $viewSorters = $defaultGridParameters[ParametersInterface::SORT_PARAMETERS];
        }
        $parameters->set('_sort_by', $viewSorters);
    }
}
