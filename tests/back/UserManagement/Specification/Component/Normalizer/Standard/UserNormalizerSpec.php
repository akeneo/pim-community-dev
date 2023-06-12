<?php

namespace Specification\Akeneo\UserManagement\Component\Normalizer\Standard;

use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Normalizer\Standard\UserNormalizer;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserNormalizerSpec extends ObjectBehavior
{
    function let(DateTimeNormalizer $dateTimeNormalizer)
    {
        $this->beConstructedWith($dateTimeNormalizer);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_has_a_cacheable_supports_method()
    {
        $this->shouldImplement(CacheableSupportsMethodInterface::class);
        $this->hasCacheableSupportsMethod()->shouldBe(true);
    }

    function it_is_a_standard_user_normalizer(UserInterface $user)
    {
        $this->shouldHaveType(UserNormalizer::class);
        $this->supportsNormalization(new \stdClass, 'standard')->shouldBe(false);
        $this->supportsNormalization($user, 'internal_api')->shouldBe(false);
        $this->supportsNormalization($user, 'standard')->shouldBe(true);
        $this->supportsNormalization($user, 'array')->shouldBe(true);
    }

    function it_normalizes_a_user(UserInterface $user, FileInfoInterface $avatar, DatagridView $productGridView, DateTimeNormalizer $dateTimeNormalizer)
    {
        $role = new Role();
        $role->setRole('ROLE_ADMIN');
        $enUS = new Locale();
        $enUS->setCode('en_US');
        $salesTree = new Category();
        $salesTree->setCode('sales');
        $print = new Channel();
        $print->setCode('print');
        $avatar->getKey()->willReturn('a/b/c/123456avatar.png');
        $avatar->getOriginalFilename()->willReturn('avatar.png');

        $user->getUserIdentifier()->willReturn('johndoe');
        $user->isEnabled()->willReturn(true);
        $user->getNamePrefix()->willReturn(null);
        $user->getFirstName()->willReturn('John');
        $user->getMiddleName()->willReturn(null);
        $user->getLastName()->willReturn('Doe');
        $user->getNameSuffix()->willReturn(null);
        $user->getPhone()->willReturn('+330000000');
        $user->getEmail()->willReturn('john.doe@example.null');
        $user->getAvatar()->willReturn($avatar);
        $user->getCatalogLocale()->willReturn($enUS);
        $user->getCatalogScope()->willReturn($print);
        $user->getDefaultTree()->willReturn($salesTree);
        $user->getTimezone()->willReturn('Europe/Paris');
        $user->getUiLocale()->willReturn($enUS);
        $user->getGroupNames()->willReturn(['IT support']);
        $user->getRolesCollection()->willReturn(new ArrayCollection([$role]));
        $user->getProductGridFilters()->willReturn(['family', 'name']);
        $productGridView->getLabel()->willReturn('Incomplete accessories ecommerce');
        $user->getDefaultGridView('product-grid')->willReturn($productGridView);
        $createdAt = new \DateTime('2018-03-20 18:13:09.000000');
        $user->getCreatedAt()->willReturn($createdAt);
        $updatedAt = new \DateTime('2018-04-20 18:13:09.000000');
        $user->getUpdatedAt()->willReturn($updatedAt);
        $lastLogin = new \DateTime('2019-04-20 18:13:09.000000');
        $user->getLastLogin()->willReturn($lastLogin);
        $dateTimeNormalizer->normalize($createdAt, 'standard', [])->willReturn('2018-03-20T18:13:00+00:00');
        $dateTimeNormalizer->normalize($updatedAt, 'standard', [])->willReturn('2018-04-20T18:13:00+00:00');
        $dateTimeNormalizer->normalize($lastLogin,'standard', [])->willReturn('2019-04-20T18:13:00+00:00');
        $user->getLoginCount()->willReturn(15);

        $this->normalize($user, 'standard')->shouldReturn(
            [
                'username' => 'johndoe',
                'enabled' => true,
                'name_prefix' => null,
                'first_name' => 'John',
                'middle_name' => null,
                'last_name' => 'Doe',
                'name_suffix' => null,
                'phone' => '+330000000',
                'email' => 'john.doe@example.null',
                'avatar' => [
                    'filePath' => 'a/b/c/123456avatar.png',
                    'originalFilename' => 'avatar.png',
                ],
                'catalog_default_locale' => 'en_US',
                'catalog_default_scope' => 'print',
                'default_category_tree' => 'sales',
                'user_default_locale' => 'en_US',
                'timezone' => 'Europe/Paris',
                'groups' => ['IT support'],
                'roles' => ['ROLE_ADMIN'],
                'product_grid_filters'=> ['family', 'name'],
                'default_product_grid_view' => 'Incomplete accessories ecommerce',
                'date_account_created' => '2018-03-20T18:13:00+00:00',
                'date_account_last_updated' => '2018-04-20T18:13:00+00:00',
                'last_logged_in' => '2019-04-20T18:13:00+00:00',
                'login_count' => 15,
            ]
        );
    }
}
