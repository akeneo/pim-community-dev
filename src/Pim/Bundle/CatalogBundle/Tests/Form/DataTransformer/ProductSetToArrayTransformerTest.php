<?php
namespace Pim\Bundle\CatalogBundle\Form\Type;

use Pim\Bundle\CatalogBundle\Doctrine\ProductManager;
use Pim\Bundle\CatalogBundle\Tests\KernelAwareTest;
use Pim\Bundle\CatalogBundle\Form\DataTransformer\ProductSetToArrayTransformer;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductSetToArrayTransformerTest extends KernelAwareTest
{

    /**
     * Test related method
     */
    public function testTransform()
    {
        // get first set
        $productManager = $this->container->get('pim.catalog.product_manager');
        $productTemplateManager = $this->get('pim.catalog.product_template_manager');
        $set = $productManager->getSetRepository()->findOneByCode('base');
        $this->assertNotNull($set);

        // transform to array
        $transformer = new ProductSetToArrayTransformer($productManager, $productTemplateManager);
        $data = $transformer->transform($set);

        // assert
        $this->assertCompareArrayAndSet($data, $set);

        // move one attribute from existing group to a new
        $oldGroup = $set->getGroups()->first();
        $oldAttributes = array();
        foreach ($oldGroup->getAttributes() as $att) {
            $oldAttributes[]= $att->getId();
            // add only one
            break;
        }

        // add attribute no yet in this set
        $repo = $productManager->getAttributeRepository();
        $othersAttributes = $repo->findAllExcept($set);
        foreach ($othersAttributes as $att) {
            // add only one
            $oldAttributes[]= $att->getId();
        }

        // remove group
        $oldGroupTwo = $set->getGroups()->next();
        unset($data['groups'][$oldGroupTwo->getCode()]);

        // add new group with removed attribute
        $timestamp = str_replace('.', '', microtime(true));
        $grpCode = 'new-grp'.$timestamp;
        $data['groups'][$grpCode]= array(
            'id'    => null,
            'code'  => $grpCode,
            'title' => 'new-title',
            'attributes' => $oldAttributes
        );

        $set = $transformer->reverseTransform($data);
        $this->assertCompareArrayAndSet($data, $set);
    }

    /**
     * Test related method
     */
    public function testReverseTransform()
    {
        $productManager = $this->container->get('pim.catalog.product_manager');
        $productTemplateManager = $this->get('pim.catalog.product_template_manager');

        // transform array to new set
        $transformer = new ProductSetToArrayTransformer($productManager, $productTemplateManager);
        $data = array (
            'id'     => null,
            'code'   => 'new-set',
            'title'  => 'my title',
            'groups' => array(
                'new-group' => array(
                    'id'    => null,
                    'code'  => 'new-group',
                    'title' => 'new-title',
                    'attributes' => array()
                )
            )
        );
        $set = $transformer->reverseTransform($data);

        // assert
        $this->assertCompareArrayAndSet($data, $set);
    }

    /**
     * refactor asserts fot transform and reverse
     *
     * @param array      $data set data
     * @param ProductSet $set  set entity
     */
    protected function assertCompareArrayAndSet($data, $set)
    {
        // base data
        $this->assertEquals($set->getCode(), $data['code']);
        $this->assertEquals($set->getTitle(), $data['title']);
        // groups
        $this->assertEquals($set->getGroups()->count(), count($data['groups']));
        foreach ($set->getGroups() as $group) {
            $this->assertEquals($group->getCode(), $data['groups'][$group->getCode()]['code']);
            $this->assertEquals($group->getTitle(), $data['groups'][$group->getCode()]['title']);
            $this->assertEquals($group->getAttributes()->count(), count($data['groups'][$group->getCode()]['attributes']));
            // attributes
            foreach ($group->getAttributes() as $attribute) {
                $this->assertTrue(in_array($attribute->getId(), $data['groups'][$group->getCode()]['attributes']));
            }
        }
    }

}
