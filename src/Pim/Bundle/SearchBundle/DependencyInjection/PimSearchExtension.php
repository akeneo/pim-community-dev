<?php

namespace Pim\Bundle\SearchBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Pim Search Bundle
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimSearchExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $entitiesConfig = $container->getParameter('oro_search.entities_config');
//         if (isset($entitiesConfig['Oro\Bundle\TagBundle\Entity\Tag'])) {
//             unset($entitiesConfig['Oro\Bundle\TagBundle\Entity\Tag']);
//         }
        if (isset($entitiesConfig['Oro\Bundle\EmailBundle\Entity\Email'])) {
            unset($entitiesConfig['Oro\Bundle\EmailBundle\Entity\Email']);
        }

        $container->setParameter('oro_search.entities_config', $entitiesConfig);
        $entitiesConfig = $container->getParameter('oro_search.entities_config');
    }
}
