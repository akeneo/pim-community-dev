<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Symfony\Component\Form\FormFactoryInterface;
use Doctrine\ORM\EntityManager;
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
     * The goal is to transform an array like this:
     * array(
     *     'code'    => 'laptops',
     *     'parent'  => 'computers',
     *     'dynamic' => '1',
     *     'left'    => '10',
     *     'level'   => '2',
     *     'right'   => '20',
     *     'title' => 'en_US:Laptops,fr_FR:Portables'
     * )
     *
     * into this:
     *
     * array(
     *     'code'    => 'laptops',
     *     'parent'  => 1,
     *     'dynamic' => 1,
     *     'left'    => 10,
     *     'level'   => 2,
     *     'right'   => 20,
     *     'title' => array(
     *         'en_US' => 'Laptops',
     *         'fr_FR' => 'Portables'
     *     )
     * )
     *
     * and to bind it to the CategoryType.
     *
     * @param mixed $item item to be processed
     *
     * @return null|Category
     *
     * @throws Exception when validation errors are present
     */
    public function process($item)
    {
        $category = $this->getCategory($item);
        $form     = $this->createAndSubmitForm($category, $item);

        if (!$form->isValid()) {
            throw new InvalidObjectException($form);
        }

        return $category;
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
            $category->setDynamic((bool) $item['dynamic']);
            $category->setLeft((int) $item['left']);
            $category->setLevel((int) $item['level']);
            $category->setRight((int) $item['right']);

            if ($item['parent']) {
                if ($parent = $this->findCategory($item['parent'])) {
                    $category->setParent($parent);
                }
            }

            $titles = explode($this->titleDelimiter, $item['title']);

            foreach ($titles as $titleItem) {
                $title = explode($this->localeDelimiter, $titleItem);
                $translation = new CategoryTranslation;
                $translation->setLocale(reset($title));
                $translation->setTitle(end($title));
                $category->addTranslation($translation);
            }
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
            $title = explode($this->localeDelimiter, $titleItem);
            $titleData[] = array(
                reset($title) => end($title)
            );
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
