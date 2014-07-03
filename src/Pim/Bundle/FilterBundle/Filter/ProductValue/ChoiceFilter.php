<?php

namespace Pim\Bundle\FilterBundle\Filter\ProductValue;

use Symfony\Component\Form\FormFactoryInterface;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\FilterBundle\Filter\AjaxChoiceFilter;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;

/**
 * Choice filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChoiceFilter extends AjaxChoiceFilter
{
    /** @var AbstractAttribute */
    protected $attribute;

    /** @var string */
    protected $optionRepoClass;

    /** @var UserContext */
    protected $userContext;

    /**
     * Constructor
     *
     * @param FormFactoryInterface $factory
     * @param ProductFilterUtility $util
     * @param UserContext          $userContext
     * @param string               $optionRepoClass
     */
    public function __construct(
        FormFactoryInterface $factory,
        ProductFilterUtility $util,
        UserContext $userContext,
        $optionRepoClass
    ) {
        parent::__construct($factory, $util);

        $this->userContext     = $userContext;
        $this->optionRepoClass = $optionRepoClass;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return false;
        }

        $operator = $this->getOperator($data['type']);

        $this->util->applyFilterByAttribute(
            $ds,
            $this->get(ProductFilterUtility::DATA_NAME_KEY),
            $data['value'],
            $operator
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        $this->loadAttribute();

        $options = array_merge(
            $this->getOr('options', []),
            ['csrf_protection' => false]
        );

        $options['field_options']     = isset($options['field_options']) ? $options['field_options'] : [];
        $options['choice_url']        = 'pim_ui_ajaxentity_list';
        $options['choice_url_params'] = $this->getChoiceUrlParams();
        $options['preload_choices']   = $this->attribute->getMinimumInputLength() < 1;

        if (!$this->form) {
            $this->form = $this->formFactory->create($this->getFormType(), [], $options);
        }

        return $this->form;
    }

    /**
     * Load the attribute for this filter
     * Required to prepare choice url params and filter configuration
     *
     * @throws \LogicException
     */
    protected function loadAttribute()
    {
        if (null === $this->attribute) {
            $fieldName = $this->get(ProductFilterUtility::DATA_NAME_KEY);
            $attribute = $this->util->getAttribute($fieldName);

            if (!$attribute) {
                throw new \LogicException(sprintf('There is no product attribute with code %s.', $fieldName));
            }

            $this->attribute = $attribute;
        }
    }

    /**
     * @return array
     */
    protected function getChoiceUrlParams()
    {
        return [
            'class'        => $this->optionRepoClass,
            'dataLocale'   => $this->userContext->getCurrentLocaleCode(),
            'collectionId' => $this->attribute->getId()
        ];
    }
}
