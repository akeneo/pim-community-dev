<?php

namespace Pim\Bundle\FilterBundle\Datasource\Orm;

use Oro\Bundle\FilterBundle\Datasource\Orm\OrmFilterDatasourceAdapter as OroOrmFilterDatasourceAdapter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType;

/**
 * Customize the OroPlatform datasource adapter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OrmFilterDatasourceAdapter extends OroOrmFilterDatasourceAdapter
{
    /**
     * Return value format depending on comparison type
     *
     * @param string $comparisonType
     *      
     * @return string
     */
    public function getFormatByComparisonType($comparisonType)
    {
        switch ($comparisonType) {
            case TextFilterType::TYPE_STARTS_WITH:
                $format = '%s%%';
                break;
            case TextFilterType::TYPE_ENDS_WITH:
                $format = '%%%s';
                break;
            case TextFilterType::TYPE_CONTAINS:
            case TextFilterType::TYPE_NOT_CONTAINS:
                $format = '%%%s%%';
                break;
            default:
                $format = '%s';
        }

        return $format;
    }
}
