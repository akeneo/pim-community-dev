<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Symfony\Component\Form\FormFactoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Pim\Bundle\ImportExportBundle\AbstractConfigurableStepElement;
use Pim\Bundle\ImportExportBundle\Exception\InvalidObjectException;
use Pim\Bundle\ProductBundle\Entity\Category;
use Pim\Bundle\ProductBundle\Entity\CategoryTranslation;

/**
 * Category form processor
 * Allows to bind data to the category form and validate it
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidCategoryCreationProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface
{
    protected $entityManager;
    protected $formFactory;

    protected $titleDelimiter  = ',';
    protected $localeDelimiter = ':';

    protected $data;
    protected $categories;

    /**
     * Constructor
     *
     * @param EntityManager        $entityManager
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(EntityManager $entityManager, FormFactoryInterface $formFactory)
    {
        $this->entityManager = $entityManager;
        $this->formFactory   = $formFactory;
    }

    /**
     * Set the title delimiter
     *
     * @param string $titleDelimiter
     */
    public function setTitleDelimiter($titleDelimiter)
    {
        $this->titleDelimiter = $titleDelimiter;
    }

    /**
     * Get the Title delimiter
     *
     * @return string
     */
    public function getTitleDelimiter()
    {
        return $this->titleDelimiter;
    }

    /**
     * Set the locale delimiter
     *
     * @param string $localeDelimiter
     */
    public function setLocaleDelimiter($localeDelimiter)
    {
        $this->localeDelimiter = $localeDelimiter;
    }

    /**
     * Get the Locale delimiter
     *
     * @return string
     */
    public function getLocaleDelimiter()
    {
        return $this->localeDelimiter;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigurationFields()
    {
        return array(
            'titleDelimiter' => array(),
            'localeDelimiter' => array()
        );
    }

    /**
     * Receives an array of categories and processes them
     *
     * @param mixed $data Data to be processed
     *
     * @return array:Category
     */
    public function process($data)
    {
        $this->data = $data;
        $this->categories = new ArrayCollection;

        foreach ($this->data as $item) {
            $this->processItem($item);
        }

        foreach ($this->categories as $category) {
            $parent = array_filter(
                $this->data,
                function ($item) use ($category) {
                    return $item['code'] === $category->getCode();
                }
            );
            $parentCode = $parent ? reset($parent)['parent'] : null;
            if ($parentCode) {
                $this->addParent($category, $parentCode);
            }
        }

        return $this->categories->toArray();
    }

    /**
     * Transforms a category to a form-compatible format and binds it to the CategoryType
     * If the category is valid, it is stored into the categories property
     *
     * @param array $item
     *
     * @throws Exception when validation errors are present
     */
    private function processItem($item)
    {
        $category = $this->getCategory($item);
        $form     = $this->createAndSubmitForm($category, $item);

        if (!$form->isValid()) {
            throw new InvalidObjectException($form);
        }

        $this->categories[] = $category;
    }

    /**
     * Assigns a parent to the category
     *
     * @param Category $category
     * @param string   $parentCode
     *
     * @return void
     */
    private function addParent(Category $category, $parentCode)
    {
        $parent = $this->findCategory($parentCode);

        if ($parent) {
            $category->setParent($parent);
        } else {
            $parent = $this->categories->filter(
                function ($category) use ($parentCode) {
                    return $category->getCode() === $parentCode;
                }
            )->first();

            if ($parent) {
                $category->setParent($parent);
            } else {
                $this->processInvalidParent($parentCode);
            }
        }
    }

    /**
     * Recursively removes categories with invalid parent categories
     *
     * @param string $parentCode
     *
     * @return void
     */
    private function processInvalidParent($parentCode)
    {
        $invalidItems = array_filter(
            $this->data,
            function ($item) use ($parentCode) {
                return $item['parent'] === $parentCode;
            }
        );

        $invalidCodes = array_map(
            function ($category) {
                return $category['code'];
            },
            $invalidItems
        );

        foreach ($invalidCodes as $code) {
            $this->categories = $this->categories->filter(
                function ($category) use ($code) {
                    if ($category->getCode() === $code) {
                        // TODO: Log an error = this category can't be imported because it has an invalid parent
                        // somewhere in the category tree

                        return false;
                    }

                    return true;
                }
            );

            $this->processInvalidParent($code);
        }
    }

    /**
     * Create a category
     *
     * @param array $item
     *
     * @return Product
     */
    private function getCategory(array $item)
    {
        $category = $this->findCategory($item['code']);

        if (!$category) {
            $category = new Category;
            $category->setCode($item['code']);

            $titles = explode($this->titleDelimiter, $item['title']);

            foreach ($titles as $titleItem) {
                $title = explode($this->localeDelimiter, $titleItem);
                $translation = new CategoryTranslation;
                $translation->setLocale(reset($title));
                $translation->setTitle(end($title));
                $category->addTranslation($translation);
            }
        }

        $category->setDynamic((bool) $item['dynamic']);

        return $category;
    }

    /**
     * Create and submit the category form
     *
     * @param Category $category
     * @param array    $item
     *
     * @return FormInterface
     */
    private function createAndSubmitForm(Category $category, array $item)
    {
        $form = $this->formFactory->create(
            'pim_category',
            $category,
            array(
                'csrf_protection' => false
            )
        );

        $titleData = array();

        $titles = explode($this->titleDelimiter, $item['title']);
        foreach ($titles as $titleItem) {
            $title = explode($this->localeDelimiter, $titleItem);
            $titleData[reset($title)] = end($title);
        }

        $data = array(
            'code' => $item['code'],
            'title' => $titleData
        );

        $form->submit($data);

        return $form;
    }

    /**
     * Find category by code
     * @param string $code
     *
     * @return Category|null
     */
    private function findCategory($code)
    {
        return $this->entityManager ->getRepository('PimProductBundle:Category')->findOneBy(array('code' => $code));
    }
}
