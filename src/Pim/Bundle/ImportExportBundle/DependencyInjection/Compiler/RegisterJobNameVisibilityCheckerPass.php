<?php

namespace Pim\Bundle\ImportExportBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This compiler pass allows you to add job name or its parameter to the visibility checker
 * It is reusable in an extension or a project
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RegisterJobNameVisibilityCheckerPass implements CompilerPassInterface
{
    /** @var string[] */
    protected $jobNames;

    /** @staticvar string */
    const VISIBILITY_CHECKER_ID = 'pim_import_export.view_element.visibility_checker.job_name';

    /**
     * @param string[] $jobNames
     */
    public function __construct(array $jobNames)
    {
        $this->jobNames = $jobNames;
    }

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(static::VISIBILITY_CHECKER_ID)) {
            return;
        }

        $providerDefinition = $container->getDefinition(static::VISIBILITY_CHECKER_ID);
        foreach ($this->jobNames as $jobName) {
            if ($container->hasParameter($jobName)) {
                $jobName = $container->getParameter($jobName);
            }
            $providerDefinition->addMethodCall('addJobName', [$jobName]);
        }
    }
}
