<?php

namespace Pim\Bundle\FilterBundle\Filter\Product;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\FilterBundle\Filter\AjaxChoiceFilter;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;
use Pim\Bundle\UserBundle\Context\UserContext;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Product groups filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupsFilter extends AjaxChoiceFilter
{
    /** @var string */
    protected $groupClass;

    /** @var UserContext */
    protected $userContext;

    /**
     * Constructor
     *
     * @param FormFactoryInterface $factory
     * @param ProductFilterUtility $util
     * @param UserContext          $userContext
     * @param string               $groupClass
     */
    public function __construct(
        FormFactoryInterface $factory,
        ProductFilterUtility $util,
        UserContext $userContext,
        $groupClass
    ) {
        parent::__construct($factory, $util);

        $this->userContext = $userContext;
        $this->groupClass  = $groupClass;
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

        $ids = $data['value'];
        $this->util->applyFilter($ds, 'groups.id', 'IN', $ids);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        $options = array_merge(
            $this->getOr('options', []),
            ['csrf_protection' => false]
        );

        $options['field_options']     = isset($options['field_options']) ? $options['field_options'] : [];
        $options['choice_url']        = 'pim_ui_ajaxentity_list';
        $options['choice_url_params'] = $this->getChoiceUrlParams();
        $options['preload_choices']   = false;

        if (!$this->form) {
            $this->form = $this->formFactory->create($this->getFormType(), [], $options);
        }

        return $this->form;
    }

    /**
     * @return array
     */
    protected function getChoiceUrlParams()
    {
        return [
            'class'        => $this->groupClass,
            'dataLocale'   => $this->userContext->getCurrentLocaleCode(),
            'collectionId' => null
        ];
    }
}
