<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

abstract class AbstractCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Add definitions and parameters from $containerData to $containerBuilder
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder
     * @param array $containerData
     */
    protected function addDataToContainerBuilder(ContainerBuilder $containerBuilder, array $containerData)
    {
        if (!empty($containerData['definitions'])) {
            $containerBuilder->addDefinitions($containerData['definitions']);
        }

        if (!empty($containerData['parameters'])) {
            foreach ($containerData['parameters'] as $name => $value) {
                $containerBuilder->setParameter($name, $value);
            }
        }
    }

    /**
     * Asserts $containerBuilder has definitions with expected data
     *
     * @param ContainerBuilder $containerBuilder
     * @param array $expectedDefinitionsData
     */
    protected function assertContainerBuilderHasExpectedDefinitions(
        ContainerBuilder $containerBuilder,
        array $expectedDefinitionsData
    ) {
        foreach ($expectedDefinitionsData as $serviceId => $expectedData) {
            $definition = $containerBuilder->getDefinition($serviceId);
            $this->assertInstanceOf('Symfony\Component\DependencyInjection\Definition', $definition);

            $this->assertDefinitionHasExpectedData($definition, $expectedData, "Service '$serviceId'");
        }
    }

    /**
     * Asserts $definition has expected arguments, calls, scope, class
     *
     * @param Definition $definition
     * @param array $expectedData
     * @param string $subjectName
     */
    protected function assertDefinitionHasExpectedData(
        Definition $definition,
        $expectedData,
        $subjectName = 'Definition'
    ) {
        foreach ($expectedData as $name => $value) {
            switch ($name) {
                case 'methodCalls':
                    foreach ($value as $method => $arguments) {
                        $this->assertDefinitionHasMethodCall($definition, $method, $arguments, $subjectName);
                    }
                    break;
                case 'noMethodCalls':
                    foreach ($value as $method) {
                        $this->assertFalse(
                            $definition->hasMethodCall($method),
                            "$subjectName not expected to have method call '$method' arguments."
                        );
                    }
                    break;
                case 'arguments':
                    foreach ($value as $index => $argument) {
                        $this->assertEquals(
                            $argument,
                            $definition->getArgument($index),
                            "$subjectName does not have expected argument at index $index."
                        );
                    }
                    break;
                default:
                    $method = 'get' . ucfirst($name);
                    $this->assertEquals($value, $definition->$method(), "$subjectName does not have expected $name.");
            }
        }
    }

    /**
     * Asserts definition has expected method call with arguments
     *
     * @param Definition $definition
     * @param string $method
     * @param array $expectedArguments
     * @param string $subjectName
     */
    private function assertDefinitionHasMethodCall(
        Definition $definition,
        $method,
        array $expectedArguments,
        $subjectName = 'Definition'
    ) {
        $callFound = false;
        foreach ($definition->getMethodCalls() as $call) {
            list($actualMethod, $actualArguments) = $call;
            if ($method == $actualMethod) {
                $this->assertEquals(
                    $expectedArguments,
                    $actualArguments,
                    "$subjectName does not have expected method call '$method' arguments."
                );
                $callFound = true;
                break;
            }
        }
        $this->assertTrue(
            $callFound,
            "$subjectName does not have expected method call '$method'."
        );
    }

    /**
     * @param array $tags
     * @return Definition
     */
    protected function createStubDefinitionWithTags(array $tags = array())
    {
        return $this->createStubDefinition(null, array(), $tags);
    }

    /**
     * @param string $class
     * @param array $arguments
     * @param array $tags
     * @return Definition
     */
    protected function createStubDefinition($class = null, $arguments = array(), array $tags = array())
    {
        if (!$class) {
            $class = uniqid('StubDefinitionClassName');
        }
        $result = new Definition($class, $arguments);

        foreach ($tags as $tag => $attributes) {
            $result->addTag($tag, $attributes);
        }
        return $result;
    }
}
