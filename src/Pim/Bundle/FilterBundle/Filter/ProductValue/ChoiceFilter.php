<?php

namespace Pim\Bundle\FilterBundle\Filter\ProductValue;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\FilterBundle\Filter\AjaxChoiceFilter;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
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
    /** @var AttributeInterface */
    protected $attribute;

    /** @var string */
    protected $optionRepoClass;

    /** @var UserContext */
    protected $userContext;

    /** @var AttributeRepository */
    protected $attributeRepository;

    /**
     * Constructor
     *
     * @param FormFactoryInterface $factory
     * @param ProductFilterUtility $util
     * @param UserContext          $userContext
     * @param string               $optionRepoClass
     * @param AttributeRepository  $attributeRepository
     */
    public function __construct(
        FormFactoryInterface $factory,
        ProductFilterUtility $util,
        UserContext $userContext,
        $optionRepoClass,
        AttributeRepository $attributeRepository
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
            $attribute = $this->attributeRepository->findOneByCode($fieldName);

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
