<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\AttributeGrid;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\Quality;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Entity\AttributeQuality;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Doctrine\ORM\Query\Expr;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceInterface as PimDatasourceInterface;
use Webmozart\Assert\Assert;

class AddQualityDataExtension extends AbstractExtension
{
    public const ATTRIBUTE_QUALITY_ALIAS = 'attribute_quality';

    /** @var FeatureFlag */
    private $featureFlag;

    public function __construct(FeatureFlag $featureFlag, RequestParameters $requestParams = null)
    {
        parent::__construct($requestParams);

        $this->featureFlag = $featureFlag;
    }

    public function isApplicable(DatagridConfiguration $config)
    {
        return $this->featureFlag->isEnabled() && 'attribute-grid' === $config->getName();
    }

    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        Assert::implementsInterface($datasource, PimDatasourceInterface::class);
        $qb = $datasource->getQueryBuilder();
        $rootAlias = current($qb->getRootAliases());

        $qb
            ->addSelect(sprintf("COALESCE(%s.quality, '%s') AS quality", self::ATTRIBUTE_QUALITY_ALIAS, Quality::PROCESSING))
            ->leftJoin(
                AttributeQuality::class,
                self::ATTRIBUTE_QUALITY_ALIAS,
                Expr\Join::WITH,
                (string) $qb->expr()->eq($rootAlias . '.code', self::ATTRIBUTE_QUALITY_ALIAS . '.attributeCode')
            );
    }
}
