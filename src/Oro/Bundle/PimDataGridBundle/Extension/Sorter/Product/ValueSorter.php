<?php

namespace Oro\Bundle\PimDataGridBundle\Extension\Sorter\Product;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\PimDataGridBundle\Extension\Sorter\SorterInterface;

/**
 * Product value sorter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValueSorter implements SorterInterface
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(DatasourceInterface $datasource, $field, $direction)
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($field);

        $context = [];
        if (null !== $attribute) {
            if (!$attribute->isScopable()) {
                $context['scope'] = null;
            }
            if (!$attribute->isLocalizable()) {
                $context['locale'] = null;
            }
        }

        $datasource->getProductQueryBuilder()->addSorter($field, $direction, $context);
    }
}
