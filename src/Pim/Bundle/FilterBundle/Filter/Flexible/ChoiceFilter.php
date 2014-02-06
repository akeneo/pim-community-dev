<?php

namespace Pim\Bundle\FilterBundle\Filter\Flexible;

use Symfony\Component\Form\FormFactoryInterface;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\FilterBundle\Filter\AjaxChoiceFilter;
use Pim\Bundle\UserBundle\Context\UserContext;

/**
 * Flexible filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChoiceFilter extends AjaxChoiceFilter
{
    /** @var integer */
    protected $attributeId;

    /** @var string */
    protected $optionRepositoryClass;

    /** @var UserContext */
    protected $userContext;

    /**
     * Constructor
     *
     * @param FormFactoryInterface $factory
     * @param FilterUtility        $util
     * @param UserContext          $userContext
     * @param string               $optionRepositoryClass
     */
    public function __construct(
        FormFactoryInterface $factory,
        FilterUtility $util,
        UserContext $userContext,
        $optionRepositoryClass
    ) {
        parent::__construct($factory, $util);

        $this->userContext           = $userContext;
        $this->optionRepositoryClass = $optionRepositoryClass;
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

        $fen = $this->get(FilterUtility::FEN_KEY);
        $this->util->applyFlexibleFilter(
            $ds,
            $fen,
            $this->get(FilterUtility::DATA_NAME_KEY),
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
        $options = array_merge(
            $this->getOr('options', []),
            ['csrf_protection' => false]
        );

        $options['field_options']     = isset($options['field_options']) ? $options['field_options'] : [];
        $options['choice_url']        = 'pim_ui_ajaxentity_list';
        $options['choice_url_params'] = $this->getChoiceUrlParams();

        if (!$this->form) {
            $this->form = $this->formFactory->create($this->getFormType(), [], $options);
        }

        return $this->form;
    }

    /**
     * @return array
     * @throws \LogicException
     */
    protected function getChoiceUrlParams()
    {
        if (null === $this->attributeId) {
            $filedName       = $this->get(FilterUtility::DATA_NAME_KEY);
            $flexibleManager = $this->util->getFlexibleManager($this->get(FilterUtility::FEN_KEY));

            $attribute = $flexibleManager->getAttributeRepository()->findOneBy(
                ['entityType' => $flexibleManager->getFlexibleName(), 'code' => $filedName]
            );

            if (!$attribute) {
                throw new \LogicException(sprintf('There is no flexible attribute with name %s.', $filedName));
            }

            $this->attributeId = $attribute->getId();
        }

        return [
            'class'        => $this->optionRepositoryClass,
            'dataLocale'   => $this->userContext->getCurrentLocaleCode(),
            'collectionId' => $this->attributeId
        ];
    }
}
