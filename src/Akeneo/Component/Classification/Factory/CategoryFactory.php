<?php

namespace Akeneo\Component\Classification\Factory;

use Akeneo\Component\Classification\Model\CategoryInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryFactory
{
    /** @var string */
    protected $className;

    /**
     * @param string $categoryClass
     */
    public function __construct($categoryClass)
    {
        $this->className = $categoryClass;
    }

    /**
     * @return CategoryInterface
     */
    public function create()
    {
        return new $this->className();
    }

    /**
     * @return string
     */
    public function getCategoryClass()
    {
        return $this->className;
    }
}
