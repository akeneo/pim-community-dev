<?php

namespace Oro\Bundle\GridBundle\Datagrid\Views;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\GridBundle\Datagrid\Datagrid;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;

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
     * @return boolean
     */
    public function applyToDatagrid(Datagrid $datagrid, $defaultGridParameters)
    {
        $datagrid->setViewsList($this);
        $parameters = $datagrid->getParameters();

        $additionalParams = $parameters->get(ParametersInterface::ADDITIONAL_PARAMETERS);
        $viewName =  isset($additionalParams[self::PARAM_KEY]) ? $additionalParams[self::PARAM_KEY] : false;

        $view = $viewName ? $this->getViewByName($viewName) : false;
        if ($view === false) {
            return false;
        }

        // set filters
        $viewFilters = $view->getFiltersData();
        $currentFilters = $parameters->get(ParametersInterface::FILTER_PARAMETERS);
        //$defaultFilters = $defaultGridParameters[ParametersInterface::FILTER_PARAMETERS];

        $viewFilters = array_merge($currentFilters, $viewFilters);


        $parameters->set(ParametersInterface::FILTER_PARAMETERS, false);
        $parameters->set(ParametersInterface::FILTER_PARAMETERS, $viewFilters);

        // set sorters
        $viewSorters = $view->getSortersData();
        if (empty($viewSorters)) {
            $viewSorters = $defaultGridParameters[ParametersInterface::SORT_PARAMETERS];
        }
        $parameters->set(ParametersInterface::SORT_PARAMETERS, false);
        $parameters->set(ParametersInterface::SORT_PARAMETERS, $viewSorters);

        return true;
    }
}
