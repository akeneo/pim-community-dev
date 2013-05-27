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
     * Override apply filter to build query without locale and scope which are defined by the flexible manager
     *
     * {@inheritdoc}
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
                if ($filter->getName() !== 'locale' || $filter->getName() !== 'scope') {
                    $filter->apply($this->query, $data);
                }
            }
        }
    }
}