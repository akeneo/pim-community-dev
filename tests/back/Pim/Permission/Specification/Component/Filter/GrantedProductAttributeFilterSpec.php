<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Permission\Component\Filter;

use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Pim\Permission\Component\Filter\GrantedProductAttributeFilter;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\GetAllViewableLocalesForUser;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\GetViewableAttributeCodesForUserInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class GrantedProductAttributeFilterSpec extends ObjectBehavior
{
    function let(
        AttributeFilterInterface $productAttributeFilter,
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodesForUser,
        GetAllViewableLocalesForUser $getViewableLocalesForUser,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user
    ) {
        $user->getId()->willReturn(42);
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);
        $getViewableLocalesForUser->fetchAll(42)->willReturn(['en_US', 'fr_FR']);

        $this->beConstructedWith(
            $productAttributeFilter,
            $getViewableAttributeCodesForUser,
            $getViewableLocalesForUser,
            $tokenStorage
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GrantedProductAttributeFilter::class);
    }

    function it_is_an_attribute_filter()
    {
        $this->shouldImplement(AttributeFilterInterface::class);
    }

    function it_filters_when_attributes_and_locales_are_granted(
        AttributeFilterInterface $productAttributeFilter,
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodesForUser
    ) {
        $data = [
            'identifier' => 'tshirt',
            'family' => 'Summer Tshirt',
            'values' => [
                'name' => [
                    [
                        'locale' => 'en_US',
                        'scope' => null,
                        'data' => 'My very awesome T-shirt',
                    ],
                ],
                123 => [
                    [
                        'locale' => 'en_US',
                        'scope' => null,
                        'data' => 'Test with numeric attribute name',
                    ],
                ],
            ],
        ];

        $getViewableAttributeCodesForUser->forAttributeCodes(Argument::is(['name', '123']), 42)->willReturn(['name', '123']);
        $productAttributeFilter->filter($data)->willReturn($data);

        $this->filter($data)->shouldReturn($data);
    }

    function it_throws_exception_when_filters_locale_not_granted(
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodesForUser
    ) {
        $data = [
            'identifier' => 'tshirt',
            'family' => 'Summer Tshirt',
            'values' => [
                'name' => [
                    [
                        'locale' => 'en_GB',
                        'scope' => null,
                        'data' => 'My very awesome T-shirt',
                    ],
                ],
            ],
        ];

        $getViewableAttributeCodesForUser->forAttributeCodes(['name'], 42)->willReturn(['name']);

        $this->shouldThrow(
            UnknownPropertyException::class
        )->during(
            'filter',
            [$data]
        );
    }

    function it_throws_exception_when_filters_attribute_not_granted(
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodesForUser
    ) {
        $data = [
            'identifier' => 'tshirt',
            'family' => 'Summer Tshirt',
            'values' => [
                'name' => [
                    [
                        'locale' => 'en_US',
                        'scope' => null,
                        'data' => 'My very awesome T-shirt',
                    ],
                ],
            ],
        ];
        $getViewableAttributeCodesForUser->forAttributeCodes(['name'], 42)->willReturn([]);

        $this->shouldThrow(
            UnknownPropertyException::class
        )->during(
            'filter',
            [$data]
        );
    }

    function it_throws_an_exception_when_no_user_is_authenticated(
        TokenInterface $token
    ) {
        $token->getUser()->willReturn(null);
        $this->shouldThrow(
            new \RuntimeException('Could not find any authenticated user')
        )->during('filter', [['any_data']]);
    }

    function it_does_not_check_permissions_for_system_user(
        AttributeFilterInterface $productAttributeFilter,
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodesForUser,
        UserInterface $user
    ) {
        $user->getId()->willReturn(null);
        $user->getUsername()->willReturn(UserInterface::SYSTEM_USER_NAME);

        $data = [
            'identifier' => 'tshirt',
            'family' => 'Summer Tshirt',
            'values' => [
                'name' => [
                    [
                        'locale' => 'en_US',
                        'scope' => null,
                        'data' => 'My very awesome T-shirt',
                    ],
                ],
            ],
        ];
        $productAttributeFilter->filter($data)->willReturn(['filtered_data']);;

        $getViewableAttributeCodesForUser->forAttributeCodes(Argument::cetera())->shouldNotBeCalled();
        $this->filter($data)->shouldReturn(['filtered_data']);
    }
}
