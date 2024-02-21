<?php

namespace Oro\Bundle\PimFilterBundle\Filter\ProductValue;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\PimFilterBundle\Filter\AjaxChoiceFilter;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
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
    /** @var UserContext */
    protected $userContext;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var AttributeOptionRepositoryInterface */
    protected $attributeOptionRepository;

    /**
     * @param FormFactoryInterface               $factory
     * @param ProductFilterUtility               $util
     * @param UserContext                        $userContext
     * @param AttributeRepositoryInterface       $attributeRepository
     * @param AttributeOptionRepositoryInterface $attributeOptionRepository
     */
    public function __construct(
        FormFactoryInterface $factory,
        ProductFilterUtility $util,
        UserContext $userContext,
        AttributeRepositoryInterface $attributeRepository,
        AttributeOptionRepositoryInterface $attributeOptionRepository
    ) {
        parent::__construct($factory, $util);

        $this->userContext = $userContext;
        $this->attributeRepository = $attributeRepository;
        $this->attributeOptionRepository = $attributeOptionRepository;
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

        if (Operators::IN_LIST === $operator || Operators::NOT_IN_LIST === $operator) {
            $filteredValues = $this->filterOnlyExistingOptions($data['value']);
        } else {
            $filteredValues = $data['value'];
        }

        $this->util->applyFilter(
            $ds,
            $this->get(ProductFilterUtility::DATA_NAME_KEY),
            $operator,
            $filteredValues
        );

        return true;
    }

    /**
     * Load the attribute for this filter
     * Required to prepare choice url params and filter configuration
     *
     * @throws \LogicException
     *
     * @return AttributeInterface
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
     * {@inheritdoc}
     */
    protected function getFormOptions()
    {
        $attribute = $this->getAttribute();

        return array_merge(
            parent::getFormOptions(),
            [
                'choice_url' => 'pim_ui_ajaxentity_list',
                'choice_url_params' => [
                    'class' => $this->attributeOptionRepository->getClassName(),
                    'dataLocale' => $this->userContext->getCurrentLocaleCode(),
                    'collectionId' => $attribute->getId(),
                    'options' => [
                        'type' => 'code',
                    ],
                ],
            ]
        );
    }

    /**
     * Filter options value to have only existing option codes
     *
     * @param $optionCodes
     * @return array
     */
    private function filterOnlyExistingOptions($optionCodes)
    {
        $attribute = $this->getAttribute();
        $attributeOptions = $this->attributeOptionRepository->findCodesByIdentifiers(
            $attribute->getCode(),
            $optionCodes
        );
        $existingOptionCodes = array_column($attributeOptions, 'code');

        return array_values(array_intersect($optionCodes, $existingOptionCodes));
    }
}
