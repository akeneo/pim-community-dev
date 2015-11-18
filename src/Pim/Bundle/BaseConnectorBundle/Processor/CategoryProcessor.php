<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\BaseConnectorBundle\Validator\Import\ImportValidatorInterface;
use Pim\Bundle\TransformBundle\Cache\DoctrineCache;
use Pim\Bundle\TransformBundle\Transformer\EntityTransformerInterface;
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
     * @var DoctrineCache
     */
    protected $doctrineCache;

    /**
     * If true, category data will be checked to make sure that there are no circular references between the categories
     *
     * @var bool
     */
    protected $circularRefsChecked = true;

    /**
     * Constructor
     *
     * @param ImportValidatorInterface   $validator
     * @param TranslatorInterface        $translator
     * @param EntityTransformerInterface $transformer
     * @param ManagerRegistry            $managerRegistry
     * @param string                     $class
     * @param DoctrineCache              $doctrineCache
     */
    public function __construct(
        ImportValidatorInterface $validator,
        TranslatorInterface $translator,
        EntityTransformerInterface $transformer,
        ManagerRegistry $managerRegistry,
        $class,
        DoctrineCache $doctrineCache
    ) {
        parent::__construct($validator, $translator, $transformer, $managerRegistry, $class);
        $this->doctrineCache = $doctrineCache;
    }

    /**
     * Set circularRefsChecked
     *
     * @param bool $circularRefsChecked
     */
    public function setCircularRefsChecked($circularRefsChecked)
    {
        $this->circularRefsChecked = $circularRefsChecked;
    }

    /**
     * Is circularRefsChecked
     *
     * @return bool
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
        return [
            'circularRefsChecked' => [
                'type'    => 'switch',
                'options' => [
                    'label' => 'pim_base_connector.import.circularRefsChecked.label',
                    'help'  => 'pim_base_connector.import.circularRefsChecked.help'
                ]
            ],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return CategoryInterface[]
     */
    public function process($data)
    {
        $categories = [];
        $parents = [];
        $items = [];

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
        $invalidCodes = [];
        foreach ($categories as $code => $category) {
            $parentCode = $parents[$code];
            if (!$parentCode) {
                continue;
            }

            if (isset($categories[$parentCode])) {
                $parent = $categories[$parentCode];
            } else {
                $parent = $this->doctrineCache->find($this->class, $parentCode);
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
                    [
                        'parent' => [
                            ['No category with code %code%', ['%code%' => $parents[$code]]]
                        ]
                    ]
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
                [],
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
     */
    private function checkCircularReferences(array &$categories, array $items)
    {
        $invalidCodes = [];
        $checkParent = function ($category, $visited = []) use (&$invalidCodes, &$checkParent) {
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
                [
                    'parent' => [
                        ['Circular reference']
                    ]
                ]
            );
        }
    }
}
