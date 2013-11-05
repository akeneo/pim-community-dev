<?php

namespace Pim\Bundle\CatalogBundle\Twig;

use Entity\Category;

use Doctrine\ORM\EntityManager;

class CategoryExtension extends \Twig_Extension
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getFilters()
    {
        return array(
            'count_products' => new \Twig_Filter_Method($this, 'countProducts')
        );
    }

    public function countProducts($category, $nested)
    {
        // TODO : test instance of
        if ($category instanceof Category) {
            return 'pouic';
        } else {
            throw \Twig_Error_Runtime('"count_products" filter is only allowed for CategoryInterface');
        }
    }

    public function getName()
    {
        return 'pim_category_extension';
    }
}
