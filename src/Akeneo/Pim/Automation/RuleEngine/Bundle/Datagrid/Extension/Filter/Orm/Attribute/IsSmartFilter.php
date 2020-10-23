<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Bundle\Datagrid\Extension\Filter\Orm\Attribute;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\BooleanFilter;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface as PimFilterDatasourceAdapterInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Webmozart\Assert\Assert;

/**
 * Filter for the smart property of attribute
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class IsSmartFilter extends BooleanFilter
{
    /** @var string */
    protected $attributeClass;

    /** @var string */
    protected $relationClass;

    /**
     * @param FormFactoryInterface $factory
     * @param FilterUtility        $util
     * @param string               $attributeClass
     * @param string               $relationClass
     */
    public function __construct(FormFactoryInterface $factory, FilterUtility $util, $attributeClass, $relationClass)
    {
        parent::__construct($factory, $util);

        $this->attributeClass = $attributeClass;
        $this->relationClass = $relationClass;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->parseData($data);

        if (!$data || !isset($data['value'])) {
            return false;
        }

        Assert::implementsInterface($ds, PimFilterDatasourceAdapterInterface::class);
        $qb = $ds->getQueryBuilder();
        $rootAlias = current($qb->getRootAliases());

        $qb
            ->leftJoin(
                $this->relationClass,
                'rlr',
                'WITH',
                $qb->expr()->andX(
                    $qb->expr()->eq('rlr.resourceId', sprintf('%s.id', $rootAlias)),
                    $qb->expr()->eq('rlr.resourceName', $qb->expr()->literal($this->attributeClass))
                )
            );

        switch ($data['value']) {
            case BooleanFilterType::TYPE_YES:
                $expr = $qb->expr()->isNotNull('rlr.resourceId');
                break;
            case BooleanFilterType::TYPE_NO:
            default:
                $expr = $qb->expr()->isNull('rlr.resourceId');
                break;
        }

        $qb->andWhere($expr);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        $metadata = parent::getMetadata();

        $metadata[FilterUtility::TYPE_KEY] = 'boolean';

        return $metadata;
    }
}
