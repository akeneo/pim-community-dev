<?php

namespace Context;

use Behat\MinkExtension\Context\RawMinkContext;

/**
 * Context for data transformations
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TransformationContext extends RawMinkContext
{
    /**
     * @param string $code
     *
     * @Transform /^category "([^"]*)"$/
     *
     * @return Category
     */
    public function castCategoryCodeToCategory($code)
    {
        return $this->getFixturesContext()->getCategory($code);
    }

    /**
     * @return FixturesContext
     */
    private function getFixturesContext()
    {
        return $this->getMainContext()->getSubcontext('fixtures');
    }
}
