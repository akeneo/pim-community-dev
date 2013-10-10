<?php

namespace Oro\Bundle\EntityExtendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;

/**
 * EntityExtendBundle controller.
 * @Route("/entity/extend")
 * TODO: Discuss ACL impl., currently acl is disabled
 */
class ApplyController extends Controller
{
    /**
     * @Route(
     *      "/update/{id}",
     *      name="oro_entityextend_update",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0}
     * )
     * Acl(
     *      id="oro_entityextend_update",
     *      label="Apply entityconfig changes",
     *      type="action",
     *      group_name=""
     * )
     * @Template()
     */
    public function updateAction($id)
    {
        set_time_limit(0);

        /** @var KernelInterface $kernel */
        $kernel = $this->get('kernel');

        $console = escapeshellarg($this->getPhp()) . ' ' . escapeshellarg($kernel->getRootDir() . '/console');
        $env     = $kernel->getEnvironment();

        $commands = array(
            'update'       => new Process($console . ' oro:entity-extend:update-config --env ' . $env),
            'schemaUpdate' => new Process($console . ' doctrine:schema:update --force --env ' . $env),
            'searchIndex'  => new Process($console . ' oro:search:create-index --env ' . $env),
        );

        // put system in maintenance mode
        $this->get('oro_platform.maintenance')->on();

        register_shutdown_function(
            function ($mode) {
                $mode->off();
            },
            $this->get('oro_platform.maintenance')
        );

        foreach ($commands as $command) {
            /** @var $command Process */
            $command->run();
        }

        return $this->redirect($this->generateUrl('oro_entityconfig_index'));
    }

    protected function getPhp()
    {
        $phpFinder = new PhpExecutableFinder();
        if (!$phpPath = $phpFinder->find()) {
            throw new \RuntimeException(
                'The php executable could not be found, add it to your PATH environment variable and try again'
            );
        }

        return $phpPath;
    }
}
