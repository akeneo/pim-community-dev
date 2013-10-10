<?php

namespace Oro\Bundle\DataGridBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Extension\ExtensionVisitorInterface;

class Datagrid implements DatagridInterface
{
    /** @var ExtensionVisitorInterface[] */
    protected $extensions;

    /**
     * {@inheritDoc}
     */
    public function addExtension(ExtensionVisitorInterface $extension)
    {
        $this->extensions[] = $extension;
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        // TODO: Implement getData() method.
    }

}
