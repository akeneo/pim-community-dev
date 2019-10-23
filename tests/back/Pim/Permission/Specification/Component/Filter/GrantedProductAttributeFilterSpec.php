<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Permission\Component\Filter;

use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Pim\Permission\Component\Filter\GrantedProductAttributeFilter;
use Akeneo\Pim\Permission\Component\Query\GetAllViewableLocalesForUser;
use Akeneo\Pim\Permission\Component\Query\GetViewableAttributeCodesForUserInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class GrantedProductAttributeFilterSpec extends ObjectBehavior
{
    function let(
        AttributeFilterInterface $productAttributeFilter,
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodesForUser,
        GetAllViewableLocalesForUser $getViewableLocalesForUser,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token
    ) {
        $user = new User();
        $user->setId(42);
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
                '123' => [
                    [
                        'locale' => 'en_US',
                        'scope' => null,
                        'data' => 'Test with numeric attribute name',
                    ],
                ],
            ],
        ];

        $getViewableAttributeCodesForUser->forAttributeCodes(['name', '123'], 42)->willReturn(['name', '123']);
        $productAttributeFilter->filter($data)->willReturn($data);

        $this->filter($data)->shouldReturn($data);
    }

    function it_throws_exception_when_filters_locale_not_granted(
        AttributeFilterInterface $productAttributeFilter,
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
}
