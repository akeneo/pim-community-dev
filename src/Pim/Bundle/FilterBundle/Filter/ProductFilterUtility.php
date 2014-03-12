<?php

namespace Pim\Bundle\FilterBundle\Filter;

use Oro\Bundle\FilterBundle\Filter\FilterUtility as BaseFilterUtility;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;

/**
 * Product filter utility
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductFilterUtility extends BaseFilterUtility
{
    const FEN_KEY         = 'flexible_entity_name';
    const PARENT_TYPE_KEY = 'parent_type';

    /** @var FlexibleManagerRegistry */
    protected $fmr;

    /**
     * @param FlexibleManagerRegistry $fmr
     */
    public function __construct(FlexibleManagerRegistry $fmr)
    {
        $this->fmr = $fmr;
    }

    /**
     * {@inheritdoc}
     */
    public function getParamMap()
    {
        return [self::PARENT_TYPE_KEY => self::TYPE_KEY];
    }

    /**
     * Gets flexible manager
     *
     * @param string $flexibleEntityName
     *
     * @throws \LogicException
     * @return FlexibleManager
     */
    public function getFlexibleManager($flexibleEntityName)
    {
        if (!$flexibleEntityName) {
            throw new \LogicException('Flexible entity filter must have flexible entity name.');
        }

        return $this->fmr->getManager($flexibleEntityName);
    }

    /**
     * Applies filter to query by flexible attribute
     *
     * @param FilterDatasourceAdapterInterface $ds
     * @param string                           $flexibleEntityName
     * @param string                           $field
     * @param mixed                            $value
     * @param string                           $operator
     */
    public function applyFlexibleFilter(
        FilterDatasourceAdapterInterface $ds,
        $flexibleEntityName,
        $field,
        $value,
        $operator
    ) {
        $manager = $this->getFlexibleManager($flexibleEntityName);

        $attributeRepo = $manager->getAttributeRepository();
        $attribute = $attributeRepo->findOneByEntityAndCode($flexibleEntityName, $field);

        $repository = $manager->getFlexibleRepository();
        if ($attribute) {
            $repository->applyFilterByAttribute($ds->getQueryBuilder(), $attribute, $value, $operator);
        } else {
            $repository->applyFilterByField($ds->getQueryBuilder(), $field, $value, $operator);
        }
    }
}
