<?php
namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Pim\Bundle\CatalogBundle\Tests\KernelAwareTest;
use Pim\Bundle\CatalogTaxinomyBundle\Entity\Channel;
use Pim\Bundle\CatalogTaxinomyBundle\Form\Type\ChannelType;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelTypeTest extends KernelAwareTest
{

    /**
     * Test related method
     */
    public function testBuildForm()
    {
        $productManager = $this->container->get('pim.catalog.product_manager');
        $entity = new Channel();
        $this->container->get('form.factory')->create(new ChannelType(), $entity);
    }

}
