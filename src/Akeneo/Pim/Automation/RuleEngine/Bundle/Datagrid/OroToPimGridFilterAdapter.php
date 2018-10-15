<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Bundle\Datagrid;

use Oro\Bundle\PimDataGridBundle\Adapter\OroToPimGridFilterAdapter as BaseAdapter;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\MassActionDispatcher;

/**
 * Transform Oro filters into Akeneo PIM filters
 *
 * @author Julien Janvier <j.janvier@gmail.com>
 */
class OroToPimGridFilterAdapter extends BaseAdapter
{
    /**
     * @param MassActionDispatcher $massActionDispatcher
     */
    public function __construct(MassActionDispatcher $massActionDispatcher)
    {
        parent::__construct($massActionDispatcher);
    }

    /**
     * {@inheritdoc}
     */
    public function adapt(array $parameters)
    {
        return ['values' => $this->massActionDispatcher->dispatch($parameters)];
    }
}
