<?php

namespace Oro\Bundle\EntityExtendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Annotation\Acl;

use Oro\Bundle\EntityConfigBundle\Config\ConfigModelManager;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

/**
 * EntityExtendBundle controller.
 * @Route("/entity/extend")
 * @Acl(
 *      id="oro_entityextend",
 *      name="Entity extend manipulation",
 *      description="Entity extend manipulation"
 * )
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
     * @Acl(
     *      id="oro_entityextend_update",
     *      name="Apply changes",
     *      description="Apply entityconfig changes",
     *      parent="oro_entityextend"
     * )
     * @Template()
     */
    public function updateAction($id)
    {
        /** @var EntityConfigModel $entity */
        $entity = $this->getDoctrine()->getRepository(EntityConfigModel::ENTITY_NAME)->find($id);

        /** @var KernelInterface $kernel */
        $kernel = $this->get('kernel');
        $application = new Application($this->get('kernel'));
        $application->setAutoExit(false);

        // put system in maintenance mode
        $this->get('oro_platform.maintenance')->on();

        $output = new StreamOutput(
            fopen($kernel->getLogDir() . DIRECTORY_SEPARATOR . 'bap_install.log', 'w+')
        );

        register_shutdown_function(
            function ($mode) {
                $mode->off();
            },
            $this->get('oro_platform.maintenance')
        );

//        $application->run(
//            new ArrayInput(
//                array(
//                    'command' => 'oro:entity-extend:backup',
//                    'entity'  => str_replace('\\', '\\\\', $entity->getClassName()),
//                )
//            ),
//            $output
//        );
        $application->run(
            new ArrayInput(
                array(
                    'command' => 'oro:entity-extend:dump',
                )
            ),
            $output
        );

        $kernel->getBundle('OroEntityExtendBundle')->boot();

        $application->run(
            new ArrayInput(
                array(
                    'command' => 'doctrine:schema:update',
                    '--force' => true
                )
            ),
            $output
        );
        $application->run(
            new ArrayInput(
                array(
                    'command' => 'oro:search:create-index',
                )
            )
            ,
            $output
        );

        return $this->redirect($this->generateUrl('oro_entityconfig_index'));
    }
}
