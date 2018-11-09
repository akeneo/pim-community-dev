<?php

namespace Pim\Bundle\FilterBundle\Filter\ProductValue;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\FilterBundle\Filter\AjaxChoiceFilter;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Repository\AttributeOptionRepositoryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
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

    /** @var AttributeOptionRepositoryInterface */
    protected $attributeOptionRepository;

    /**
     * TODO @merge remove null on master & delete optionRepoClass
     * @param FormFactoryInterface               $factory
     * @param ProductFilterUtility               $util
     * @param UserContext                        $userContext
     * @param null|string                        $optionRepoClass
     * @param AttributeRepositoryInterface       $attributeRepository
     * @param AttributeOptionRepositoryInterface $attributeOptionRepository
     */
    public function __construct(
        FormFactoryInterface $factory,
        ProductFilterUtility $util,
        UserContext $userContext,
        $optionRepoClass,
        AttributeRepositoryInterface $attributeRepository,
        AttributeOptionRepositoryInterface $attributeOptionRepository = null
    ) {
        parent::__construct($factory, $util);

        $this->userContext = $userContext;
        $this->optionRepoClass = $optionRepoClass;
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

        /* TODO @merge remove null condition on master*/
        if (null !== $this->attributeOptionRepository &&
            (Operators::IN_LIST === $operator || Operators::NOT_IN_LIST === $operator)
        ) {
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

        /* TODO @merge remove null condition on master, optionRepoClass to delete*/
        return array_merge(
            parent::getFormOptions(),
            [
                'choice_url' => 'pim_ui_ajaxentity_list',
                'choice_url_params' => [
                    'class' => null !== $this->attributeOptionRepository
                        ? $this->attributeOptionRepository->getClassName()
                        : $this->optionRepoClass,
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
