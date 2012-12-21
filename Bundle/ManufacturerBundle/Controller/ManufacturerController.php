<?php

namespace Oro\Bundle\ManufacturerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Manufacturer default controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/manufacturer")
 */
class ManufacturerController extends Controller
{
    /**
     * @Route("/index")
     * @Template()
     */
    public function indexAction()
    {
        $mm = $this->container->get('manufacturer_manager');
        $manufacturers = $mm->getEntityRepository()->findAll();

        return array('manufacturers' => $manufacturers);
    }

    /**
     * @Route("/insert")
     */
    public function insertAction()
    {
        // new instance
        $mm = $this->container->get('manufacturer_manager');
        $manufacturer = $mm->getNewEntityInstance();
        $manufacturer->setName('Dell');

        // save
        $mm->getStorageManager()->persist($manufacturer);
        $mm->getStorageManager()->flush();

        $this->get('session')->setFlash('notice', 'Manufacturer has been inserted');

        return $this->redirect($this->generateUrl('oro_manufacturer_manufacturer_index'));
    }
}
