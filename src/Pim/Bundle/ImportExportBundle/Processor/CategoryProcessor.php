<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;

/**
 * Valid category creation (or update) processor
 *
 * Allow to bind input data to a category and validate it
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryProcessor extends AbstractEntityProcessor
{
    /**
     * If true, category data will be checked to make sure that there are no circular references between the categories
     *
     * @var boolean
     */
    protected $circularRefsChecked = true;

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
    public function getConfigurationFields()
    {
        return array(
            'circularRefsChecked' => array(
                'type' => 'switch',
            ),
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return Category[]
     */
    public function process($data)
    {
        $this->data = new ArrayCollection($data);
        $this->entities = new ArrayCollection();

        foreach ($this->data as $item) {
            $this->processItem($item);
        }

        foreach ($this->entities as $category) {
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

        return $this->entities->toArray();
    }

    /**
     * {@inheritdoc}
     *
     * Transforms a category to a form-compatible format and binds it to the CategoryType
     */
    protected function processItem($item)
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
            $messages = array();
            foreach ($violations as $violation) {
                $messages[]= (string) $violation;
            }
            throw new InvalidItemException(implode(', ', $messages), $item);

        } else {
            $this->entities[] = $category;
        }
    }

    /**
     * Assigns a parent to the category
     *
     * @param CategoryInterface $category
     * @param string            $parentCode
     *
     * @return null
     */
    private function addParent(CategoryInterface $category, $parentCode)
    {
        if ($category->getCode() === $parentCode) {
            $this->processInvalidParent($parentCode);

            return;
        }

        $parent = $this->findCategory($parentCode);

        if ($parent) {
            $category->setParent($parent);
        } else {
            $parent = $this->entities->filter(
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
            $this->entities = $this->entities->filter(
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
        $categories = $this->entities->filter(
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
        return $this->entityManager->getRepository('PimCatalogBundle:Category')->findOneBy(array('code' => $code));
    }
}
