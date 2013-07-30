<?php

namespace Oro\Bundle\FormBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FormCompilerPass implements CompilerPassInterface
{
    const ORO_FORM_LAYOUT      = 'OroFormBundle:Form:fields.html.twig';
    const GENEMU_LAYOUT_PREFIX = 'GenemuFormBundle:Form:';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $allResources = $container->getParameter('twig.form.resources');

        if (!in_array(self::ORO_FORM_LAYOUT, $allResources)) {
            return;
        }

        // need to move genemu layouts higher to override their blocks in OroFormBundle:Form:fields.html.twig
        $genemuResources = array();
        foreach ($allResources as $key => $resource) {
            if (strpos($resource, self::GENEMU_LAYOUT_PREFIX) === 0) {
                $genemuResources[] = $resource;
                unset($allResources[$key]);
            }
        }

        $resultResources = array();
        foreach ($allResources as $resource) {
            if ($resource == self::ORO_FORM_LAYOUT) {
                $resultResources = array_merge($resultResources, $genemuResources);
            }
            $resultResources[] = $resource;
        }

        $container->setParameter('twig.form.resources', $resultResources);
    }
}
