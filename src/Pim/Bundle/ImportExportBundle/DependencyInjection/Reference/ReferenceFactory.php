<?php

namespace Pim\Bundle\ImportExportBundle\DependencyInjection\Reference;

use Symfony\Component\DependencyInjection\Reference;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceFactory
{
    public function createReference($serviceId)
    {
        return new Reference($serviceId);
    }
}
