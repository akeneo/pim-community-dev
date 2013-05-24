<?php

namespace Context\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyCreation extends Page
{
    protected $path='/product/product-family/create';

    public function getFieldLocator($name, $locale)
    {
        return sprintf('pim_product_family_name_%s:%s', strtolower($name), $locale);
    }
}
