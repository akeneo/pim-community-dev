<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\UIBundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class configuration
 *
 * @author    Marie Minasyan <marie.minasyan@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('pim_ui');

        $rootNode = $treeBuilder->getRootNode();

        SettingsBuilder::append(
            $rootNode,
            [
                'language' => ['value' => 'en_US'],
                'loading_message_enabled' => ['value' => false],
                'loading_messages' => [
                    'value' => null,
                ],
                'sandbox_banner' => ['value' => '1'],
            ]
        );

        return $treeBuilder;
    }
}
