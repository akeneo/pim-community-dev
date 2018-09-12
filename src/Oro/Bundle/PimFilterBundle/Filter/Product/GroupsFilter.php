<?php

namespace Oro\Bundle\PimFilterBundle\Filter\Product;

use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\PimFilterBundle\Filter\AjaxChoiceFilter;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
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
        $this->groupClass = $groupClass;
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

        $codes = $data['value'];
        $this->util->applyFilter($ds, 'groups', 'IN', $codes);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFormOptions()
    {
        return array_merge(
            parent::getFormOptions(),
            [
                'choice_url'        => 'pim_ui_ajaxentity_list',
                'choice_url_params' => [
                    'class'        => $this->groupClass,
                    'dataLocale'   => $this->userContext->getCurrentLocaleCode(),
                    'collectionId' => null,
                    'options'      => [
                        'type' => 'code',
                    ],
                ]
            ]
        );
    }
}
