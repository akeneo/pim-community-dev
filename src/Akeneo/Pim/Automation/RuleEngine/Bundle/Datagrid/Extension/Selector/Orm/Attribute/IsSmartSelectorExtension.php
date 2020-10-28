<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Bundle\Datagrid\Extension\Selector\Orm\Attribute;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceInterface as PimDatasourceInterface;
use Webmozart\Assert\Assert;

/**
 * Datagrid extension for attribute grid to select the smart property of attributes
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class IsSmartSelectorExtension extends AbstractExtension
{
    /** @var string */
    protected $attributeClass;

    /** @var string */
    protected $relationClass;

    /**
     * @param string            $attributeClass
     * @param string            $relationClass
     * @param RequestParameters $requestParams
     */
    public function __construct($attributeClass, $relationClass, RequestParameters $requestParams = null)
    {
        parent::__construct($requestParams);

        $this->attributeClass = $attributeClass;
        $this->relationClass = $relationClass;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        return 'attribute-grid' === $config->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        Assert::implementsInterface($datasource, PimDatasourceInterface::class);
        $qb = $datasource->getQueryBuilder();
        $rootAlias = current($qb->getRootAliases());

        $qb
            ->leftJoin(
                $this->relationClass,
                'r',
                'WITH',
                $qb->expr()->andX(
                    $qb->expr()->eq('r.resourceId', sprintf('%s.id', $rootAlias)),
                    $qb->expr()->eq('r.resourceName', $qb->expr()->literal($this->attributeClass))
                )
            )
            ->addSelect('CASE WHEN r.resourceId IS NULL THEN false ELSE true END AS is_smart')
            ->distinct(true);
    }
}
