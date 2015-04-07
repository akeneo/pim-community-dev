<?php

namespace Pim\Bundle\DataGridBundle\Adapter;

use Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;
use Symfony\Component\HttpFoundation\Request;

/**
 * Transform Oro filters into Akeneo PIM filters
 *
 * todo: make this class cleaner and faster by transforming for real the oro filters to pim filters
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OroToPimGridFilterAdapter implements GridFilterAdapterInterface
{
    /** @var MassActionDispatcher */
    protected $massActionDispatcher;

    /**
     * @param MassActionDispatcher $massActionDispatcher
     */
    public function __construct(MassActionDispatcher $massActionDispatcher)
    {
        $this->massActionDispatcher = $massActionDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function adapt(Request $request)
    {
        $products =  $this->massActionDispatcher->dispatch($request);

        $productIds = [];
        foreach ($products as $product) {
            $productIds[] = $product->getId();
        }

        $filter = ['field' => 'id', 'operator' => 'IN', 'value' => $productIds];

        return [$filter];
    }
}
