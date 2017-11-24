<?php
declare(strict_types=1);

namespace Pim\Bundle\FilterBundle\Filter\Product;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\StringFilter as OroStringFilter;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * Filter on label and identifier
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LabelOrIdentifierFilter extends OroStringFilter
{
    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data): bool
    {
        $data = $this->parseData($data);
        if (!$data) {
            return false;
        }

        $this->util->applyFilter($ds, 'label_or_identifier', Operators::CONTAINS, $data['value']);

        return true;
    }
}
