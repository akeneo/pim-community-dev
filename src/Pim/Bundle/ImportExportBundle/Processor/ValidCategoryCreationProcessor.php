<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Symfony\Component\Form\FormFactoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Pim\Bundle\ImportExportBundle\AbstractConfigurableStepElement;
use Pim\Bundle\ImportExportBundle\Exception\InvalidObjectException;
use Pim\Bundle\ProductBundle\Entity\Category;

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

    protected $titleDelimiter    = ',';
    protected $localeDelimiter   = ':';
    protected $checkCircularRefs = true;

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
     * Set checkCircularRefs
     *
     * @param boolean $checkCircularRefs
     */
    public function setCheckCircularRefs($checkCircularRefs)
    {
        $this->checkCircularRefs = $checkCircularRefs;
    }

    /**
     * Get checkCircularRefs
     *
     * @return boolean
     */
    public function getCheckCircularRefs()
    {
        return $this->checkCircularRefs;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigurationFields()
    {
        return array(
            'titleDelimiter' => array(),
            'localeDelimiter' => array(),
            'checkCircularRefs' => array(
                'type' => 'checkbox',
            ),
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
        $this->data = new ArrayCollection($data);
        $this->categories = new ArrayCollection;

        foreach ($this->data as $item) {
            $this->processItem($item);
        }

        foreach ($this->categories as $category) {
            $parent = $this->data->filter(
                function ($item) use ($category) {
                    return $item['code'] === $category->getCode();
                }
            )->first();
            $parentCode = $parent['parent'];
            if ($parentCode) {
                $this->addParent($category, $parentCode);
            } else {
                $category->setParent(null);
            }
        }

        if ($this->checkCircularRefs === true) {
            $this->checkCircularReferences();
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
        if ($category->getCode() === $parentCode) {
            $this->processInvalidParent($parentCode);

            return;
        }

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
        $invalidItems = $this->data->filter(
            function ($item) use ($parentCode) {
                return $item['parent'] === $parentCode;
            }
        );

        foreach ($invalidItems as $invalidItem) {
            $this->data->removeElement($invalidItem);
        }

        $invalidCodes = $invalidItems->map(
            function ($item) {
                return $item['code'];
            }
        );

        $em = $this->entityManager;
        foreach ($invalidCodes as $code) {
            $this->categories = $this->categories->filter(
                function ($category) use ($code, $em) {
                    if ($category->getCode() === $code) {
                        $em->detach($category);
                        foreach ($category->getTranslations() as $translation) {
                            $em->detach($translation);
                        }

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
     * Checks for circular references in the category tree
     *
     * @return void
     */
    private function checkCircularReferences()
    {
        $categories = $this->categories->filter(
            function ($category) {
                return $category->getParent() !== null;
            }
        );

        foreach ($categories as $category) {
            $this->checkParent($category, array());
        }
    }

    /**
     * Recursively finds the root parent of the category, removes the category if a circular reference is encountered
     *
     * @param Category|null $category
     * @param array         $visited
     *
     * @return void
     */
    private function checkParent($category, array $visited)
    {
        if ($category === null) {
            return;
        }

        if (isset($visited[$category->getCode()])) {
            $this->processInvalidParent($category->getCode());
        } else {
            $visited[$category->getCode()] = true;
            $this->checkParent($category->getParent(), $visited);
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
        }

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
                'csrf_protection' => false,
                'import_mode'     => true,
            )
        );

        $titleData = array();

        $titles = explode($this->titleDelimiter, $item['title']);
        foreach ($titles as $titleItem) {
            $titleItem = explode($this->localeDelimiter, $titleItem);
            if (count($titleItem) === 2) {
                list($locale, $title) = $titleItem;
                $titleData[$locale] = $title;
            } else {
                // TODO: Log an error = translation for this category will not be imported
            }
        }

        $data = array(
            'code' => $item['code'],
            'title' => $titleData,
            'dynamic' => (bool) $item['dynamic'],
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
