<?php

namespace Pim\Bundle\DataGridBundle\Datasource;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class VariantGroupProductDatasource extends ProductDatasource
{
    /** @var GroupRepositoryInterface */
    protected $groupRepository;

    /**
     * @param ObjectManager                       $om
     * @param ProductQueryBuilderFactoryInterface $factory
     * @param NormalizerInterface                 $normalizer
     * @param GroupRepositoryInterface            $groupRepository
     */
    public function __construct(
        ObjectManager $om,
        ProductQueryBuilderFactoryInterface $factory,
        NormalizerInterface $normalizer,
        GroupRepositoryInterface $groupRepository
    ) {
        parent::__construct($om, $factory, $normalizer);

        $this->groupRepository = $groupRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeQueryBuilder($method, array $config = [])
    {
        parent::initializeQueryBuilder($method, $config);

        $currentVariantGroup = $this->groupRepository->find($this->getConfiguration('current_group_id'));

        $this->addAxesFilters($currentVariantGroup);

        $this->pqb->addFilter(
            'variant_group',
            Operators::NOT_IN_LIST,
            $this->getOtherVariantGroupsCodes($currentVariantGroup)
        );

        return $this;
    }

    /**
     * @param GroupInterface $variantGroup
     */
    protected function addAxesFilters(GroupInterface $variantGroup)
    {
        /** @var AttributeInterface $attribute */
        foreach ($variantGroup->getAxisAttributes() as $attribute) {
            $this->pqb->addFilter($attribute->getCode(), Operators::IS_NOT_EMPTY, []);
        }
    }

    /**
     * @param GroupInterface $currentVariantGroup
     *
     * @return string[]
     */
    protected function getOtherVariantGroupsCodes(GroupInterface $currentVariantGroup)
    {
        $variantGroupCodes = [];

        foreach ($this->groupRepository->getAllVariantGroups() as $variantGroup) {
            if ($variantGroup->getCode() !== $currentVariantGroup->getCode()) {
                $variantGroupCodes[] = $variantGroup->getCode();
            }
        }

        return $variantGroupCodes;
    }
}
