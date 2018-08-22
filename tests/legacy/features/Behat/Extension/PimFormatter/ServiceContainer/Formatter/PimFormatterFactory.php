<?php

declare(strict_types=1);

namespace Pim\Behat\Extension\PimFormatter\ServiceContainer\Formatter;

use Behat\Behat\Output\Node\EventListener\JUnit\JUnitOutlineStoreListener;
use Behat\Behat\Output\Node\EventListener\Statistics\HookStatsListener;
use Behat\Behat\Output\Node\EventListener\Statistics\ScenarioStatsListener;
use Behat\Behat\Output\Node\EventListener\Statistics\StepStatsListener;
use Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter;
use Behat\Behat\Output\Node\Printer\JUnit\JUnitFeaturePrinter;
use Behat\Behat\Output\Node\Printer\JUnit\JUnitStepPrinter;
use Behat\Behat\Output\Node\Printer\JUnit\JUnitSuitePrinter;
use Behat\Behat\Output\Statistics\PhaseStatistics;
use Behat\Testwork\Exception\ServiceContainer\ExceptionExtension;
use Behat\Testwork\Output\Node\EventListener\ChainEventListener;
use Behat\Testwork\Output\NodeEventListeningFormatter;
use Behat\Testwork\Output\Printer\Factory\FilesystemOutputFactory;
use Behat\Testwork\Output\Printer\JUnitOutputPrinter;
use Behat\Testwork\Output\ServiceContainer\Formatter\FormatterFactory;
use Behat\Testwork\Output\ServiceContainer\OutputExtension;
use Pim\Behat\Extension\PimFormatter\Output\Node\EventListener\JUnitDurationListener;
use Pim\Behat\Extension\PimFormatter\Output\Node\EventListener\PimFeatureElementListener;
use Pim\Behat\Extension\PimFormatter\Output\Node\Printer\PimScenarioPrinter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * PIM formatter factory, based on junit factory.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @see \Behat\Behat\Output\ServiceContainer\Formatter\JUnitFormatterFactory
 */
final class PimFormatterFactory implements FormatterFactory
{
    /*
     * Available services
     */
    const ROOT_LISTENER_ID = 'pim.output.node.listener.junit';
    const RESULT_TO_STRING_CONVERTER_ID = 'pim.output.node.printer.result_to_string';

    /**
     * {@inheritdoc}
     */
    public function buildFormatter(ContainerBuilder $container)
    {
        $this->loadRootNodeListener($container);
        $this->loadPrinterHelpers($container);
        $this->loadCorePrinters($container);
        $this->loadFormatter($container);
    }

    /**
     * {@inheritdoc}
     */
    public function processFormatter(ContainerBuilder $container)
    {
    }

    /**
     * Loads printer helpers.
     *
     * @param ContainerBuilder $container
     */
    private function loadPrinterHelpers(ContainerBuilder $container) : void
    {
        $definition = new Definition(ResultToStringConverter::class);
        $container->setDefinition(self::RESULT_TO_STRING_CONVERTER_ID, $definition);
    }

    /**
     * Loads the specific printers for Akeneo PIM.
     *
     * @param ContainerBuilder $container
     */
    private function loadCorePrinters(ContainerBuilder $container) : void
    {
        $definition = new Definition(JUnitSuitePrinter::class, [
            new Reference('pim.output.junit.statistics'),
        ]);
        $container->setDefinition('pim.output.node.printer.junit.suite', $definition);

        $definition = new Definition(JUnitFeaturePrinter::class, [
            new Reference('pim.output.junit.statistics'),
        ]);
        $container->setDefinition('pim.output.node.printer.junit.feature', $definition);

        $definition = new Definition(PimScenarioPrinter::class, [
            '%paths.base%',
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
            new Reference('pim.output.node.listener.junit.outline'),
            new Reference('pim.output.node.listener.junit.duration')
        ]);
        $container->setDefinition('pim.output.node.printer.pim.scenario', $definition);

        $definition = new Definition(JUnitStepPrinter::class, [
            new Reference(ExceptionExtension::PRESENTER_ID),
        ]);
        $container->setDefinition('pim.output.node.printer.junit.step', $definition);
    }

    /**
     * Loads the node listeners required for JUnit printers to work.
     *
     * @param ContainerBuilder $container
     */
    private function loadRootNodeListener(ContainerBuilder $container) : void
    {
        $definition = new Definition(JUnitOutlineStoreListener::class, [
            new Reference('pim.output.node.printer.junit.suite')
        ]);
        $container->setDefinition('pim.output.node.listener.junit.outline', $definition);

        $definition = new Definition(JUnitDurationListener::class);
        $container->setDefinition('pim.output.node.listener.junit.duration', $definition);

        $definition = new Definition(ChainEventListener::class, [
            [
                new Reference('pim.output.node.listener.junit.outline'),
                new Reference('pim.output.node.listener.junit.duration'),
                new Definition(PimFeatureElementListener::class, [
                    new Reference('pim.output.node.printer.junit.feature'),
                    new Reference('pim.output.node.printer.pim.scenario'),
                    new Reference('pim.output.node.printer.junit.step'),
                ]),
            ],
        ]);
        $container->setDefinition(self::ROOT_LISTENER_ID, $definition);
    }

    /**
     * Loads formatter itself.
     *
     * @param ContainerBuilder $container
     */
    private function loadFormatter(ContainerBuilder $container) : void
    {
        $definition = new Definition(PhaseStatistics::class);
        $container->setDefinition('pim.output.junit.statistics', $definition);

        $definition = new Definition(NodeEventListeningFormatter::class, [
            'pim',
            'Outputs the failures in JUnit compatible files, with path to the failing scenario instead of namespace.',
            ['timer' => true],
            $this->createOutputPrinterDefinition(),
            new Definition(ChainEventListener::class, [
                [
                    new Reference(self::ROOT_LISTENER_ID),
                    new Definition(ScenarioStatsListener::class, [
                        new Reference('pim.output.junit.statistics')
                    ]),
                    new Definition(StepStatsListener::class, [
                        new Reference('pim.output.junit.statistics'),
                        new Reference(ExceptionExtension::PRESENTER_ID)
                    ]),
                    new Definition(HookStatsListener::class, [
                        new Reference('pim.output.junit.statistics'),
                        new Reference(ExceptionExtension::PRESENTER_ID)
                    ]),
                ],
            ]),
        ]);
        $definition->addTag(OutputExtension::FORMATTER_TAG, ['priority' => 110]);
        $container->setDefinition(OutputExtension::FORMATTER_TAG . '.pim', $definition);
    }

    /**
     * Creates output printer definition.
     *
     * @return Definition
     */
    private function createOutputPrinterDefinition() : Definition
    {
        return new Definition(JUnitOutputPrinter::class, [
            new Definition(FilesystemOutputFactory::class),
        ]);
    }
}
