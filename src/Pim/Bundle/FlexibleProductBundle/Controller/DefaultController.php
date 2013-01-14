<?php
namespace Pim\Bundle\FlexibleProductBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\FlexibleEntityBundle\Model\Attribute\Type\AbstractAttributeType;

/**
 * Default controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/default")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/index")
     * @Template()
     * @return mixed
     */
    public function indexAction()
    {
        $pm = $this->container->get('pim_flexibleproduct.product_manager');
        //var_dump($pm->getEntityRepository());

        $attributeCode = 'moncode';
        $attribute = $pm->getEntityRepository()->findAttributeByCode($attributeCode);
        if (!$attribute) {
            $prodAtt = $pm->createFlexibleAttribute();
            $prodAtt->setName('Pouet');
            $prodAtt->setDescription('Pouet desc');
            $prodAtt->setSmart(false);

            $prodAtt->getAttribute()->setCode($attributeCode);
            $prodAtt->getAttribute()->setBackendStorage(AbstractAttributeType::BACKEND_STORAGE_ATTRIBUTE_VALUE);
            $prodAtt->getAttribute()->setBackendType(AbstractAttributeType::BACKEND_TYPE_VARCHAR);

            $pm->getStorageManager()->persist($prodAtt);
            $pm->getStorageManager()->flush();
        }

        return $this->render('PimFlexibleProductBundle:Default:index.html.twig', array('name' => 'pouet'));
    }
}
