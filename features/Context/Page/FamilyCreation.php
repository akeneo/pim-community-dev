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
    protected $path='/enrich/family/create';

    public function getFieldLocator($name, $locale)
    {
        return sprintf('pim_family_form_%s_%s', strtolower($name), $locale);
    }

    public function getUri()
    {
        return $this->path;
    }
}
