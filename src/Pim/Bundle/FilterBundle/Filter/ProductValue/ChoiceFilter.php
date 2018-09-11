<?php

namespace Pim\Bundle\FilterBundle\Filter\ProductValue;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\FilterBundle\Filter\AjaxChoiceFilter;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Choice filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChoiceFilter extends AjaxChoiceFilter
{
    /** @var string */
    protected $optionRepoClass;

    /** @var UserContext */
    protected $userContext;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * Constructor
     *
     * @param FormFactoryInterface         $factory
     * @param ProductFilterUtility         $util
     * @param UserContext                  $userContext
     * @param string                       $optionRepoClass
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        FormFactoryInterface $factory,
        ProductFilterUtility $util,
        UserContext $userContext,
        $optionRepoClass,
        AttributeRepositoryInterface $attributeRepository
    ) {
        parent::__construct($factory, $util);

        $this->userContext = $userContext;
        $this->optionRepoClass = $optionRepoClass;
        $this->attributeRepository = $attributeRepository;
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

        $this->util->applyFilter(
            $ds,
            $this->get(ProductFilterUtility::DATA_NAME_KEY),
            $operator,
            $data['value']
        );

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
                    'class'        => $this->optionRepoClass,
                    'dataLocale'   => $this->userContext->getCurrentLocaleCode(),
                    'collectionId' => $this->params['id'],
                    'options'      => [
                        'type' => 'code',
                    ],
                ]
            ]
        );
    }
}
