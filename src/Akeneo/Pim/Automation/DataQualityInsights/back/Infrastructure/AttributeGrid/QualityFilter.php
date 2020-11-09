<?php

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
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface as PimFilterDatasourceAdapterInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Webmozart\Assert\Assert;

class QualityFilter extends ChoiceFilter
{
    public function __construct(FormFactoryInterface $factory, FilterUtility $util)
    {
        parent::__construct($factory, $util);
    }

    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->parseData($data);

        if (!$data || !isset($data['value'])) {
            return false;
        }

        Assert::implementsInterface($ds, PimFilterDatasourceAdapterInterface::class);

        if (isset($data['value'][0]) && $this->isLocaleSpecificFilter($data['value'][0])) {
            $ds->getQueryBuilder()
                ->andWhere(AddQualityDataExtension::ATTRIBUTE_LOCALE_QUALITY_ALIAS . '.quality = :quality_value')
                ->setParameter(':quality_value', Quality::TO_IMPROVE);
        } else {
            $ds->getQueryBuilder()
                ->andWhere(AddQualityDataExtension::ATTRIBUTE_QUALITY_ALIAS . '.quality = :quality_value')
                ->setParameter(':quality_value', $data['value']);
        }

        return true;
    }

    private function isLocaleSpecificFilter($value): bool
    {
        return !in_array($value, Quality::FILTERS);
    }
}
