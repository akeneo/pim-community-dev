<?php
namespace Pim\Bundle\ProductBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\Datagrid;

/**
 * Override Oro Datagrid class for Products
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductDatagrid extends Datagrid
{
    /**
     * Apply filter data to ProxyQuery
     */
    protected function applyFilters()
    {
        $form = $this->getForm();

        /** @var $filter FilterInterface */
        foreach ($this->getFilters() as $filter) {
            $filterName = $filter->getName();
            $filterForm = $form->get($filterName);
            if ($filterForm->isValid()) {
                $data = $filterForm->getData();
                if ($filter->getName() !== 'Locale') {
                    $filter->apply($this->query, $data);
                }
            }
        }
    }
}