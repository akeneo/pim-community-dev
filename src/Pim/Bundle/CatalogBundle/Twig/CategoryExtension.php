<?php

namespace Pim\Bundle\CatalogBundle\Twig;

use Pim\Bundle\CatalogBundle\Model\CategoryInterface;

use Doctrine\ORM\EntityManager;

/**
 * Twig extension to render category from twig templates
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryExtension extends \Twig_Extension
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Constructor
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            'count_products' => new \Twig_Filter_Method($this, 'countProducts')
        );
    }

    /**
     * Count products for a defined category
     * @param CategoryInterface $category
     * @param bool $nested
     *
     * @return string
     */
    public function countProducts($category, $nested)
    {
        // TODO : test instance of
        if ($category instanceof CategoryInterface) {
            return 'pouic';
        } else {
            throw \Twig_Error_Runtime('"count_products" filter is only allowed for CategoryInterface');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_category_extension';
    }
}
