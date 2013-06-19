<?php

namespace Oro\Bundle\EntityConfigBundle\Provider;

use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;

interface ConfigProviderInterface
{
    public function getScope();
}
