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
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @Route("/manufacturer")
 */
class ManufacturerController extends Controller
{
    /**
     * Get manager
     *
     * @return SimpleEntityManager
     */
    public function getManufacturerManager()
    {
        $mm = $this->container->get('manufacturer_manager');

        return $mm;
    }

    /**
     * @Route("/index")
     * @Template()
     *
     * @return multitype
     */
    public function indexAction()
    {
        $manufacturers = $this->getManufacturerManager()->getEntityRepository()->findAll();

        return array('manufacturers' => $manufacturers);
    }

    /**
     * @Route("/insert")
     *
     * @return multitype
     */
    public function insertAction()
    {
        $names = array('Dell', 'Lenovo', 'Acer', 'Asus', 'HP');
        $mm = $this->getManufacturerManager();

        // new instances if not exist
        foreach ($names as $name) {
            $manufacturer = $mm->getEntityRepository()->findByName($name);
            if (!$manufacturer) {
                $manufacturer = $mm->getNewEntityInstance();
                $manufacturer->setName($name);
                $mm->getStorageManager()->persist($manufacturer);
            }
        }

        // save
        $mm->getStorageManager()->flush();

        $this->get('session')->setFlash('notice', 'Manufacturer has been inserted');

        return $this->redirect($this->generateUrl('oro_manufacturer_manufacturer_index'));
    }
}
