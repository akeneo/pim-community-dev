<?php

declare(strict_types=1);

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
     */
    public function getAttributeGroups(): array
    {
        $this->spin(fn () => $this->find('css', '.attribute-group-link'), 'Cannot find any attribute group label');

        return array_map(static fn ($element) => $element->getHtml(), $this->findAll('css', '.attribute-group-link'));
    }

    public function selectRow(string $rowLabel): void
    {
        $this->spin(function () use ($rowLabel) {
            $row = $this->find('css', sprintf('tr:contains("%s")', $rowLabel));

            if (null === $row) {
                return false;
            }

            $checkbox = $row->find('css', '*[role="checkbox"]');

            if (null === $checkbox) {
                return false;
            }

            $checkbox->click();

            return true;
        }, sprintf('Couldn\'t find a checkbox for row "%s"', $rowLabel));
    }
}
