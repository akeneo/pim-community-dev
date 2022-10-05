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

use Doctrine\ORM\Query\Expr\Join;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\Quality;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Entity\AttributeLocaleQuality;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Entity\AttributeQuality;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceInterface as PimDatasourceInterface;
use Webmozart\Assert\Assert;

class AddQualityDataExtension extends AbstractExtension
{
    public const ATTRIBUTE_QUALITY_ALIAS = 'attribute_quality';
    public const ATTRIBUTE_LOCALE_QUALITY_ALIAS = 'attribute_locale_quality';

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

        $filters = $this->getRequestParams()->get('_filter');
        if ($this->hasLocaleSpecificFilter($filters)) {
            $this->addAttributeLocaleQualityQueryPart($qb, $rootAlias, $filters['quality']['value']);
        } else {
            $this->addAttributeQualityQueryPart($qb, $rootAlias);
        }
    }

    private function hasLocaleSpecificFilter(array $filters): bool
    {
        return isset($filters['quality']['value']) && !in_array($filters['quality']['value'], Quality::FILTERS);
    }

    private function addAttributeQualityQueryPart(QueryBuilder $qb, string $rootAlias): void
    {
        $qb
            ->addSelect(sprintf("COALESCE(%s.quality, '%s') AS quality", self::ATTRIBUTE_QUALITY_ALIAS, Quality::PROCESSING))
            ->leftJoin(
                AttributeQuality::class,
                self::ATTRIBUTE_QUALITY_ALIAS,
                Join::WITH,
                (string) $qb->expr()->eq($rootAlias . '.code', self::ATTRIBUTE_QUALITY_ALIAS . '.attributeCode')
            );
    }

    private function addAttributeLocaleQualityQueryPart(QueryBuilder $qb, string $rootAlias, string $localeFilter): void
    {
        $qb
            ->addSelect(sprintf("COALESCE(%s.quality, '%s') AS quality", self::ATTRIBUTE_LOCALE_QUALITY_ALIAS, Quality::PROCESSING))
            ->leftJoin(
                AttributeLocaleQuality::class,
                self::ATTRIBUTE_LOCALE_QUALITY_ALIAS,
                Join::WITH,
                (string) $qb->expr()->andX(
                    $qb->expr()->eq($rootAlias . '.code', self::ATTRIBUTE_LOCALE_QUALITY_ALIAS . '.attributeCode'),
                    $qb->expr()->eq(self::ATTRIBUTE_LOCALE_QUALITY_ALIAS . '.locale', ':locale')
                )
            )
            ->setParameter(':locale', $localeFilter);
    }
}
