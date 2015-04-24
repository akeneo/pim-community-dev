<?php

namespace Pim\Bundle\FilterBundle\Filter\ProductValue;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\FilterBundle\Filter\AjaxChoiceFilter;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;
use Pim\Bundle\UserBundle\Context\UserContext;
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

        $this->userContext     = $userContext;
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
    public function getForm()
    {
        $attribute = $this->getAttribute();

        $options = array_merge(
            $this->getOr('options', []),
            ['csrf_protection' => false]
        );

        $options['field_options']     = isset($options['field_options']) ? $options['field_options'] : [];
        $options['choice_url']        = 'pim_ui_ajaxentity_list';
        $options['choice_url_params'] = $this->getChoiceUrlParams($attribute);

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
    protected function getAttribute()
    {
        $fieldName = $this->get(ProductFilterUtility::DATA_NAME_KEY);
        $attribute = $this->attributeRepository->findOneByCode($fieldName);

        if (!$attribute) {
            throw new \LogicException(sprintf('There is no attribute with code %s.', $fieldName));
        }

        return $attribute;
    }

    /**
     * @param AttributeInterface $attribute
     *
     * @return array
     */
    protected function getChoiceUrlParams(AttributeInterface $attribute)
    {
        return [
            'class'        => $this->optionRepoClass,
            'dataLocale'   => $this->userContext->getCurrentLocaleCode(),
            'collectionId' => $attribute->getId()
        ];
    }
}
