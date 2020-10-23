<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Bundle\EventListener\Datagrid;

use Akeneo\Tool\Bundle\RuleEngineBundle\Doctrine\ORM\QueryBuilder\RuleQueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceInterface;
use Webmozart\Assert\Assert;

/**
 * Grid listener to configure the parameters of the rule grid in case it is displayed for a resource context
 *
 * @author Clement Gautier <clement.gautier@akeneo.com>
 */
class ConfigureRuleRelationGridListener
{
    /** @var RequestParameters */
    protected $requestParams;

    /**
     * @param RequestParameters $requestParams
     */
    public function __construct(RequestParameters $requestParams)
    {
        $this->requestParams = $requestParams;
    }

    /**
     * @param BuildAfter $event
     */
    public function configure(BuildAfter $event)
    {
        $datasource = $event->getDatagrid()->getDatasource();
        Assert::implementsInterface($datasource, DatasourceInterface::class);

        if (null !== $this->requestParams->get('resourceName', null)) {
            $ruleQueryBuilder = $datasource->getQueryBuilder();
            Assert::isInstanceOf($ruleQueryBuilder, RuleQueryBuilder::class);
            $ruleQueryBuilder->joinResource(
                $this->requestParams->get('resourceName'),
                $this->requestParams->get('resourceId')
            );
        }
    }
}
