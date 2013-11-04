<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Symfony\Component\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Pim\Bundle\CatalogBundle\Entity\Category;

/**
 * Valid category creation (or update) processor
 *
 * Allow to bind input data to a category and validate it
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidCategoryCreationProcessor extends AbstractConfigurableStepElement implements
    ItemProcessorInterface,
    StepExecutionAwareInterface
{
    /**
     * Entity manager
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * If true, category data will be checked to make sure that there are no circular references between the categories
     *
     * @var boolean
     */
    protected $circularRefsChecked = true;

    /**
     * Property for storing data during execution
     *
     * @var ArrayCollection
     */
    protected $data;

    /**
     * Property for storing valid categories during execution
     *
     * @var ArrayCollection
     */
    protected $categories;

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    /**
     * Constructor
     *
     * @param EntityManager      $entityManager
     * @param ValidatorInterface $validator
     */
    public function __construct(
        EntityManager $entityManager,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->validator     = $validator;
    }

    /**
     * Set circularRefsChecked
     *
     * @param boolean $circularRefsChecked
     */
    public function setCircularRefsChecked($circularRefsChecked)
    {
        $this->circularRefsChecked = $circularRefsChecked;
    }

    /**
     * Is circularRefsChecked
     *
     * @return boolean
     */
    public function isCircularRefsChecked()
    {
        return $this->circularRefsChecked;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array(
            'circularRefsChecked' => array(
                'type' => 'switch',
            ),
        );
    }

    /**
     * Receives an array of categories and processes them
     *
     * @param mixed $data Data to be processed
     *
     * @return Category[]
     */
    public function process($data)
    {
        $this->data = new ArrayCollection($data);
        $this->categories = new ArrayCollection();

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

        if ($this->circularRefsChecked === true) {
            $this->checkCircularReferences();
        }

        return $this->categories->toArray();
    }

    /**
     * Transforms a category to a form-compatible format and binds it to the CategoryType
     * If the category is valid, it is stored into the categories property
     *
     * @param array $item
     */
    private function processItem($item)
    {
        $category = $this->getCategory($item);

        $category->setCode($item['code']);
        $category->setDynamic((bool) $item['dynamic']);
        foreach ($item as $key => $value) {
            if (preg_match('/^label-(.+)/', $key, $matches)) {
                $category->setLocale($matches[1]);
                $category->setLabel($value);
            }
        }

        $category->setLocale(null);

        $violations = $this->validator->validate($category);
        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $this->stepExecution->addError((string) $violation);
            }

            return;
        } else {
            $this->categories[] = $category;
        }
    }

    /**
     * Assigns a parent to the category
     *
     * @param Category $category
     * @param string   $parentCode
     *
     * @return null
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
     * @return null
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
     * @return null
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
     * @return null
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
     * @return Category
     */
    private function getCategory(array $item)
    {
        $category = $this->findCategory($item['code']);
        if (!$category) {
            $category = new Category();
        }

        return $category;
    }

    /**
     * Find category by code
     * @param string $code
     *
     * @return Category|null
     */
    private function findCategory($code)
    {
        return $this->entityManager ->getRepository('PimCatalogBundle:Category')->findOneBy(array('code' => $code));
    }
}
