<?php

namespace Pim\Bundle\CommentBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Pim comment bundle extension
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimCommentExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('builders.yml');
        $loader->load('controllers.yml');
        $loader->load('entities.yml');
        $loader->load('form_types.yml');
        $loader->load('forms.yml');
        $loader->load('managers.yml');
        $loader->load('normalizers.yml');
        $loader->load('removers.yml');
        $loader->load('repositories.yml');
        $loader->load('savers.yml');
    }
}
