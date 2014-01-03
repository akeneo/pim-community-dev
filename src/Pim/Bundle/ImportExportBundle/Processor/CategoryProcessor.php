<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\ImportExportBundle\Cache\EntityCache;
use Pim\Bundle\ImportExportBundle\Transformer\ORMTransformer;
use Pim\Bundle\ImportExportBundle\Validator\Import\ImportValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Valid category creation (or update) processor
 *
 * Allow to bind input data to a category and validate it
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryProcessor extends TransformerProcessor
{
    /**
     * @var EntityCache
     */
    protected $entityCache;

    /**
     * If true, category data will be checked to make sure that there are no circular references between the categories
     *
     * @var boolean
     */
    protected $circularRefsChecked = true;

    /**
     * Constructor
     *
     * @param ImportValidatorInterface $validator
     * @param TranslatorInterface      $translator
     * @param ORMTransformer           $transformer
     * @param EntityCache              $entityCache
     * @param string                   $class
     */
    public function __construct(
        ImportValidatorInterface $validator,
        TranslatorInterface $translator,
        ORMTransformer $transformer,
        EntityCache $entityCache,
        $class
    ) {
        parent::__construct($validator, $translator, $transformer, $class);
        $this->entityCache = $entityCache;
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
    public function getConfigurationFields()
    {
        return array(
            'circularRefsChecked' => array(
                'type'    => 'switch',
                'options' => array(
                    'label' => 'pim_import_export.import.circularRefsChecked.label',
                    'help'  => 'pim_import_export.import.circularRefsChecked.help'
                )
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
        $categories = array();
        $parents = array();
        $items = array();

        foreach ($data as $item) {
            $parents[$item['code']] = isset($item['parent']) ? $item['parent'] : null;
            unset($item['parent']);
            if ($category = parent::process($item)) {
                $categories[$item['code']] = $category;
                $items[$item['code']] = $item;
            }
        }

        $this->setParents($categories, $parents, $items);

        if (true === $this->circularRefsChecked) {
            $this->checkCircularReferences($categories, $items);
        }

        return $categories;
    }

    /**
     * Sets the parents and recursively removes categories with bad parents
     *
     * @param array &$categories
     * @param array $parents
     * @param array $items
     */
    protected function setParents(array &$categories, array $parents, array $items)
    {
        $invalidCodes = array();
        foreach ($categories as $code => $category) {
            $parentCode = $parents[$code];
            if (!$parentCode) {
                continue;
            }

            if (isset($categories[$parentCode])) {
                $parent = $categories[$parentCode];
            } else {
                $parent = $this->entityCache->find($this->class, $parentCode);
            }

            if ($parent) {
                $category->setParent($parent);
            } else {
                $invalidCodes[] = $code;
            }
        }

        if (count($invalidCodes)) {
            foreach ($invalidCodes as $code) {
                $this->setItemErrors(
                    $items[$code],
                    array(
                        'parent' => array(
                            array('No category with code %code%', array('%code%' => $parents[$code]))
                        )
                    )
                );
                unset($categories[$code]);
            }
            $this->setParents($categories, $parents, $items);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function setItemErrors(array $item, array $errors)
    {
        if ($this->stepExecution) {
            $this->stepExecution->incrementSummaryInfo('skip');
            $this->stepExecution->addWarning(
                $this->getName(),
                implode("\n", $this->getErrorMessages($errors)),
                $item
            );
        } else {
            parent::setItemErrors($item, $errors);
        }
    }

    /**
     * Checks for circular references in the category tree
     *
     * @param array $categories
     * @param array $items
     *
     * @return null
     */
    private function checkCircularReferences(array $categories, array $items)
    {
        $invalidCodes = array();
        $checkParent = function ($category, $visited = array()) use (&$invalidCodes, &$checkParent) {
            if ($category === null) {
                return;
            }
            $invalid = in_array($category->getCode(), $visited);
            $visited[] = $category->getCode();
            if ($invalid) {
                $invalidCodes = array_merge($visited, $invalidCodes);
            } else {
                $checkParent($category->getParent(), $visited);
            }
        };

        foreach ($categories as $category) {
            if (null !== $category->getParent()) {
                $checkParent($category);
            }
        }

        foreach (array_unique($invalidCodes) as $code) {
            unset($categories[$code]);
            $this->setItemErrors(
                $items[$code],
                array(
                    'parent' => array(
                        array('Circular reference')
                    )
                )
            );
        }
    }
}
