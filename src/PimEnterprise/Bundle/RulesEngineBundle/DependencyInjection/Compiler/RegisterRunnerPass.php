<?php

namespace Pim\Bundle\RulesEngineBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegisterRunnerPass implements CompilerPassInterface
{
    // REGISTER RunnerInterface tagged services by priority
}
