<?php

namespace Pim\Behat\Decorator\Common;

/**
 * Decorate attribute add select element
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeAddSelectDecorator extends AbstractAddSelectDecorator
{
    /** @var string */
    protected $baseClass = '.add-attribute';

    /** @var string */
    protected $groupSearchString = 'li .group-label:contains("%s"), li.select2-no-results';

    /**
     * @param $item
     * @param $group
     *
     * @return mixed
     */
    public function hasAvailableAttributeInGroup($item, $group)
    {
        $group = $this->openDropList()
            ->evaluateSearch($item)
            ->getGroupFromResult($group);

        $this->spin(function () use ($item, $group) {
            return $group->getParent()->find(
                'css',
                sprintf('.attribute-label:contains("%s")', $item)
            );
        }, 'Can not find attribute label for group that was found');

        $this->closeDropList();
    }

    protected function getGroupFromResult($group)
    {
        return $this->spin(function () use ($group) {
            return $this->getResultListElement()->find('css', sprintf($this->groupSearchString, $group));
        }, 'Cannot find element in the attribute list');
    }
}
