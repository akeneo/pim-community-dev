<?php

namespace Oro\Bundle\FilterBundle\Filter;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;

abstract class AbstractFilter implements FilterInterface
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var FilterUtility */
    protected $util;

    /** @var string */
    protected $name;

    /** @var array */
    protected $params;

    /** @var Form */
    protected $form;

    /** @var FormBuilderInterface */
    protected $formBuilder;

    /**
     * Constructor
     *
     * @param FormFactoryInterface $factory
     * @param FilterUtility        $util
     */
    public function __construct(FormFactoryInterface $factory, FilterUtility $util)
    {
        $this->formFactory = $factory;
        $this->util = $util;
    }

    /**
     * {@inheritDoc}
     */
    public function init($name, array $params)
    {
        $this->name = $name;
        $this->params = $params;
    }

    /**
     * {@inheritDoc}
     */
    public function getForm()
    {
        if (!$this->form) {
            $this->form = $this->formFactory->create(
                $this->getFormType(),
                [],
                array_merge($this->getOr(FilterUtility::FORM_OPTIONS_KEY, []), ['csrf_protection' => false])
            );
        }

        return $this->form;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        $formBuilderType = $this->getFormBuilder()->get('type');
        $operatorChoices = $formBuilderType->getOption('choices');

        $choices = [];
        foreach ($operatorChoices as $choice => $key) {
            $choices[] = new ChoiceView($key, (string) $key, $choice);
        }

        $defaultMetadata = [
            'name'                     => $this->getName(),
            // use filter name if label not set
            'label'                    => ucfirst($this->name),
            'choices'                  => $choices,
            FilterUtility::ENABLED_KEY => true,
        ];

        $metadata = array_diff_key(
            $this->get(),
            array_flip($this->util->getExcludeParams())
        );
        $metadata = $this->mapParams($metadata);
        $metadata = array_merge($defaultMetadata, $metadata);

        return $metadata;
    }

    /**
     * Returns form type associated to this filter
     *
     * @return mixed
     */
    abstract protected function getFormType();

    /**
     * @return FormBuilderInterface
     */
    protected function getFormBuilder()
    {
        if (!$this->formBuilder) {
            $this->formBuilder = $this->formFactory->createBuilder(
                $this->getFormType(),
                [],
                array_merge($this->getOr(FilterUtility::FORM_OPTIONS_KEY, []), ['csrf_protection' => false])
            );
        }

        return $this->formBuilder;
    }

    /**
     * Apply filter expression to having or where clause depending on configuration
     *
     * @param FilterDatasourceAdapterInterface $ds
     * @param mixed                            $expression
     */
    protected function applyFilterToClause(FilterDatasourceAdapterInterface $ds, $expression)
    {
        $ds->addRestriction(
            $expression,
            $this->getOr(FilterUtility::CONDITION_KEY, FilterUtility::CONDITION_AND),
            $this->getOr(FilterUtility::BY_HAVING_KEY, false)
        );
    }

    /**
     * Get param or throws exception
     *
     * @param string $paramName
     *
     * @throws \LogicException
     * @return mixed
     */
    protected function get($paramName = null)
    {
        $value = $this->params;

        if ($paramName !== null) {
            if (!isset($this->params[$paramName])) {
                throw new \LogicException(sprintf('Trying to access not existing parameter: "%s"', $paramName));
            }

            $value = $this->params[$paramName];
        }

        return $value;
    }

    /**
     * Get param if exists or default value
     *
     * @param string $paramName
     * @param null   $default
     *
     * @return mixed
     */
    protected function getOr($paramName = null, $default = null)
    {
        if ($paramName !== null) {
            return isset($this->params[$paramName]) ? $this->params[$paramName] : $default;
        }

        return $this->params;
    }

    /**
     * Process mapping params
     *
     * @param array $params
     *
     * @return array
     */
    protected function mapParams($params)
    {
        $keys = [];
        $paramMap = $this->util->getParamMap();
        foreach (array_keys($params) as $key) {
            if (isset($paramMap[$key])) {
                $keys[] = $paramMap[$key];
            } else {
                $keys[] = $key;
            }
        }

        return array_combine($keys, array_values($params));
    }
}
