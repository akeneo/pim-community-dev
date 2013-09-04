<?php

namespace Oro\Bundle\ConfigBundle\Provider;

interface ProviderInterface
{
    public function getSubtreeData($treeName, $subtreeRootName);

    public function getForm($groupName);
}
