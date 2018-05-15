<?php
declare(strict_types=1);

namespace PimEnterprise\Bundle\SuggestDataBundle\Datagrid\Product;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionExtension;

/**
 * Add suggest data mass action button in product grid.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class BuildProductDatagridEventListener
{
    /**
     * @param BuildBefore $event
     */
    public function addSuggestDataMassActionButton(BuildBefore $event): void
    {
        $configuration = $event->getConfig();
        $massAction = [
            'type' => 'mass_suggest_data',
//            'acl_resource' => 'pimee_enrich_product_mass_suggest_data',
            'label' => 'pimee.grid.mass_action.mass_suggest_data',
            'className' => 'AknButton AknButton--action AknButtonList-item'
        ];

        $configuration->offsetSetByPath(
            sprintf('[%s][%s]', MassActionExtension::ACTION_KEY, 'mass_suggest_data'),
            $massAction
        );
    }
}
