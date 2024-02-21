<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\Field;

use Akeneo\Pim\Enrichment\Component\Product\Model\Group;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\ClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupFieldClearerSpec extends ObjectBehavior
{
    function it_is_a_clearer()
    {
        $this->shouldImplement(ClearerInterface::class);
    }

    function it_supports_only_groups_field()
    {
        $this->supportsProperty('categories')->shouldReturn(false);
        $this->supportsProperty('groups')->shouldReturn(true);
        $this->supportsProperty('other')->shouldReturn(false);
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

    function it_throws_an_exception_if_the_subject_is_not_a_product()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                ProductModel::class,
                ProductInterface::class
            )
        )->during('clear', [new ProductModel(), 'groups']);
    }
}
