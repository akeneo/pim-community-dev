<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Processor;

use Pim\Bundle\ImportExportBundle\Processor\ValidCategoryCreationProcessor;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Entity\CategoryTranslation;

/**
 * Test related class
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidCategoryCreationProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->em          = $this->mock('Doctrine\ORM\EntityManager');
        $this->formFactory = $this->mock('Symfony\Component\Form\FormFactory');

        $this->processor = new ValidCategoryCreationProcessor($this->em, $this->formFactory);
    }

    /**
     * Test getter/setter for titleDelimiter
     */
    public function testGetSetTitleDelimiter()
    {
        $this->assertEquals(',', $this->processor->getTitleDelimiter());
        $newDelimiter = ';';
        $this->processor->setTitleDelimiter($newDelimiter);
        $this->assertEquals($newDelimiter, $this->processor->getTitleDelimiter());
    }

    /**
     * Test getter/setter for circularRefsChecked
     */
    public function testGetSetLocaleDelimiter()
    {
        $this->assertEquals(':', $this->processor->getLocaleDelimiter());
        $newDelimiter = '|';
        $this->processor->setLocaleDelimiter($newDelimiter);
        $this->assertEquals($newDelimiter, $this->processor->getLocaleDelimiter());
    }

    /**
     * Test getter/setter for circularRefsChecked
     */
    public function testGetIsCircularRefsChecked()
    {
        $this->assertEquals(true, $this->processor->isCircularRefsChecked());
        $checkRefs = false;
        $this->processor->setCircularRefsChecked($checkRefs);
        $this->assertEquals($checkRefs, $this->processor->isCircularRefsChecked());
    }

    /**
     * Test getConfigurationFields method
     */
    public function testGetConfigurationFields()
    {
        $configurationFields = array(
            'titleDelimiter'      => array(),
            'localeDelimiter'     => array(),
            'circularRefsChecked' => array(
                'type' => 'checkbox',
            ),
        );
        $this->assertEquals($configurationFields, $this->processor->getConfigurationFields());
    }

    /**
     * Test the process method
     */
    public function testProcess()
    {
        $repository = $this->getRepositoryMock();
        $this->em->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        $categoriesMap = array(
            array(array('code' => 'root'), null, $this->getCategory('root')),
            array(array('code' => 'child1'), null, $this->getCategory('child1')),
            array(array('code' => 'child2'), null, $this->getCategory('child2')),
            array(array('code' => 'child3'), null, $this->getCategory('child3')),
        );

        $repository->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValueMap($categoriesMap));

        $this->formFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->getFormMock()));

        $data = $this->getValidCategoryData();
        $this->assertEquals($data['result'], $this->processor->process($data['csv']));
    }

    /**
     * @return array
     */
    protected function getValidCategoryData()
    {
        return array(
            'csv' => array(
                $this->getRow('root'),
                $this->getRow('child1', 'root'),
                $this->getRow('child2', 'root'),
                $this->getRow('child3', 'root'),
            ),
            'result' => array(
                $this->getCategory('root'),
                $this->getCategory('child1', 'root'),
                $this->getCategory('child2', 'root'),
                $this->getCategory('child3', 'root'),
            )
        );
    }

    /**
     * @expectedException Pim\Bundle\ImportExportBundle\Exception\InvalidObjectException
     */
    public function testInvalidProcess()
    {
        $repository = $this->getRepositoryMock();
        $this->em->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        $categoriesMap = array(
            array(array('code' => 'child1'), null, $this->getCategory('child1')),
            array(array('code' => 'child2'), null, $this->getCategory('child2')),
            array(array('code' => 'child3'), null, $this->getCategory('child3')),
        );

        $repository->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValueMap($categoriesMap));

        $this->formFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->getFormMock(false)));

        $data = $this->getInvalidCategoryData();
        $this->assertEquals($data['result'], $this->processor->process($data['csv']));
    }

    /**
     * @return array
     */
    protected function getInvalidCategoryData()
    {
        return array(
            'csv' => array(
                $this->getRow('root'),
                $this->getRow('child1', 'root'),
                $this->getRow('child2', 'root'),
                $this->getRow('child3', 'child3'),
            ),
            'result' => array(
                $this->getCategory('root'),
            )
        );
    }

    /**
     * Test the process method with circular references in the data
     */
    public function testProcessWithCircularRefs()
    {
        $this->em->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($this->getRepositoryMock()));

        $this->formFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->getFormMock()));

        $data = $this->getCategoryDataWithCircularRefs();
        $this->assertCount($data['result'], $this->processor->process($data['csv']));
    }

    /**
     * @return array
     */
    protected function getCategoryDataWithCircularRefs()
    {
        return array(
            'csv' => array(
                $this->getRow('root'),
                $this->getRow('child1', 'root'),
                $this->getRow('child2', 'child1'),
                $this->getRow('root2', 'child6'),
                $this->getRow('child4', 'root2'),
                $this->getRow('child5', 'root2'),
                $this->getRow('child6', 'root2'),
                $this->getRow('child7', 'child6'),
                $this->getRow('child8', 'child7'),
                $this->getRow('child9', 'child9'),
                $this->getRow('child10', 'child11'),
            ),
            'result' => 3
        );
    }

    /**
     * Create an array representing a single csv row
     * @param string $code
     * @param string $parent
     *
     * @return array
     */
    protected function getRow($code, $parent = null)
    {
        return array(
            'code'    => $code,
            'parent'  => $parent,
            'title'   => sprintf('en_US:%s (en),fr_FR:%s (fr)', ucfirst($code), ucfirst($code)),
            'dynamic' => '0',
        );
    }

    /**
     * @param string $class
     *
     * @return mixed
     */
    protected function mock($class)
    {
        return $this->getMockBuilder($class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @param boolean $valid
     *
     * @return Form
     */
    protected function getFormMock($valid = true)
    {
        $form = $this->mock('Symfony\Component\Form\Form');

        $form->expects($this->any())
            ->method('isValid')
            ->will($this->returnValue($valid));

        $form->expects($this->any())
            ->method('getErrors')
            ->will($this->returnValue(array()));

        return $form;
    }

    /**
     * @return EntityRepository
     */
    protected function getRepositoryMock()
    {
        return $this->mock('Doctrine\ORM\EntityRepository');
    }

    /**
     * @param sring $code
     * @param sring $parent
     *
     * @return Category
     */
    protected function getCategory($code, $parent = null)
    {
        $category = new Category();
        $category->setCode($code);

        $english = new CategoryTranslation();
        $english->setLocale('en_US');
        $english->setTitle(ucfirst($code) . ' (en)');
        $category->addTranslation($english);

        $french = new CategoryTranslation();
        $french->setLocale('fr_FR');
        $french->setTitle(ucfirst($code) . ' (fr)');
        $category->addTranslation($french);

        if ($parent) {
            $category->setParent($this->getCategory($parent));
        }

        return $category;
    }
}
