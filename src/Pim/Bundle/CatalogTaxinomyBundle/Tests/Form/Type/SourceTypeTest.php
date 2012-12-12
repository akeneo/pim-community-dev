<?php
namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Pim\Bundle\CatalogBundle\Tests\KernelAwareTest;
use Pim\Bundle\CatalogTaxinomyBundle\Entity\Source;
use Pim\Bundle\CatalogTaxinomyBundle\Form\Type\SourceType;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class SourceTypeTest extends KernelAwareTest
{

    /**
     * Test related method
     */
    public function testBuildForm()
    {
        $productManager = $this->container->get('pim.catalog.product_manager');
        $entity = new Source();
        $this->container->get('form.factory')->create(new SourceType(), $entity);
    }

}
