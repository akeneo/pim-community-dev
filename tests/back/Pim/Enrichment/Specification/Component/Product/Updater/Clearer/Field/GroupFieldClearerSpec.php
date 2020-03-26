<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\Field;

use Akeneo\Pim\Enrichment\Component\Product\Model\Group;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupFieldClearerSpec extends ObjectBehavior
{
    function it_supports_only_groups_field()
    {
        $this->supportsField('categories')->shouldReturn(false);
        $this->supportsField('groups')->shouldReturn(true);
        $this->supportsField('other')->shouldReturn(false);
    }

    function it_removes_all_groups_of_a_product()
    {

        $product = new Product();
        $groups = new ArrayCollection();
        $groups->add(new Group());
        $groups->add(new Group());
        $product->setGroups($groups);

        $this->clear($product, 'groups');
        Assert::count($product->getGroups(), 0);
    }
}
