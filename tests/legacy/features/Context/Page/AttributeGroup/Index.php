<?php

namespace Context\Page\AttributeGroup;

use Behat\Mink\Exception\ElementNotFoundException;
use Context\Page\Base\Index as BaseIndex;
use Context\Spin\TimeoutException;

/**
 * Attribute group index page
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index extends BaseIndex
{
    protected $path = '#/configuration/attribute-group/';

    /**
     * @throws ElementNotFoundException
     * @throws TimeoutException
     *
     * @return array
     */
    public function getAttributeGroups()
    {
        $this->spin(function () {
            return $this->find('css', '.attribute-group-link');
        }, 'Cannot find any attribute group label');

        return array_map(function ($element) {
            return $element->getHtml();
        }, $this->findAll('css', '.attribute-group-link'));
    }
}
