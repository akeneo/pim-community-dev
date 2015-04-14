<?php

namespace Pim\Bundle\ReferenceDataBundle\DataGrid\Filter;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;
use Pim\Bundle\FilterBundle\Filter\ProductValue\ChoiceFilter;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Reference data filter
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataFilter extends ChoiceFilter
{
    /** @var ConfigurationRegistryInterface */
    protected $registry;

    /**
     * Constructor
     *
     * @param FormFactoryInterface           $factory
     * @param ProductFilterUtility           $util
     * @param UserContext                    $userContext
     * @param AttributeRepositoryInterface   $attributeRepository
     * @param ConfigurationRegistryInterface $registry
     */
    public function __construct(
        FormFactoryInterface $factory,
        ProductFilterUtility $util,
        UserContext $userContext,
        AttributeRepositoryInterface $attributeRepository,
        ConfigurationRegistryInterface $registry
    ) {
        parent::__construct($factory, $util, $userContext, null, $attributeRepository);

        $this->registry = $registry;
    }

    /**
     * @param AttributeInterface $attribute
     *
     * @return array
     */
    protected function getChoiceUrlParams(AttributeInterface $attribute)
    {
        $referenceDataName = $attribute->getReferenceDataName();
        $referenceData = $this->registry->get($referenceDataName);
        if (null === $referenceData) {
            throw new \InvalidArgumentException(sprintf('Reference data "%s" does not exist', $referenceDataName));
        }

        return [
            'class'        => $referenceData->getClass(),
            'dataLocale'   => $this->userContext->getCurrentLocaleCode(),
            'collectionId' => $attribute->getId()
        ];
    }
}
