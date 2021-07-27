<?php

namespace Specification\Akeneo\Pim\Permission\Component\Filter;

use Akeneo\Channel\Component\Query\PublicApi\Permission\GetAllViewableLocalesForUserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Permission\Component\Filter\NotGrantedValuesFilter;
use Akeneo\Pim\Permission\Component\NotGrantedDataFilterInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\GetViewableAttributeCodesForUserInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariant;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Common\Util\ClassUtils;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class NotGrantedValuesFilterSpec extends ObjectBehavior
{
    function let(
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodes,
        GetAllViewableLocalesForUserInterface $getViewableLocaleCodesForUser,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user
    ) {
        $user->getId()->willReturn(42);
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);
        $getViewableLocaleCodesForUser->fetchAll(42)->willReturn(['en_US', 'fr_FR']);

        $this->beConstructedWith($getViewableAttributeCodes, $getViewableLocaleCodesForUser, $tokenStorage);
    }

    function it_implements_a_filter_interface()
    {
        $this->shouldImplement(NotGrantedDataFilterInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NotGrantedValuesFilter::class);
    }

    function it_removes_not_granted_values_from_an_entity_with_values_without_variation(
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodes
    ) {
        $entityWithValues = new Product();
        $entityWithValues->setValues(
            new WriteValueCollection(
                [
                    ScalarValue::value('my_text_attribute', 'Lorem ipsum'),
                    OptionValue::value('my_color_attribute', 'yellow'),
                ]
            )
        );
        $getViewableAttributeCodes->forAttributeCodes(['my_text_attribute', 'my_color_attribute'], 42)
            ->willReturn(['my_color_attribute']);

        $filteredEntity = $this->filter($entityWithValues);
        $filteredEntity->shouldBeAnInstanceOf(EntityWithValuesInterface::class);
        $filteredEntity->getValues()->shouldBeLike(
            new WriteValueCollection([OptionValue::value('my_color_attribute', 'yellow')])
        );
    }

    function it_removes_not_granted_localizable_values_from_an_entity_with_values_without_variation(
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodes
    ) {
        $entityWithValues = new Product();
        $entityWithValues->setValues(
            new WriteValueCollection(
                [
                    ScalarValue::localizableValue('my_text_attribute', 'Lorem ipsum', 'en_US'),
                    OptionValue::localizableValue('my_color_attribute', 'yellow', 'de_DE'),
                ]
            )
        );
        $getViewableAttributeCodes->forAttributeCodes(['my_text_attribute', 'my_color_attribute'], 42)
            ->willReturn(['my_text_attribute', 'my_color_attribute']);

        $filteredEntity = $this->filter($entityWithValues);
        $filteredEntity->shouldBeAnInstanceOf(EntityWithValuesInterface::class);
        $filteredEntity->getValues()->shouldBeLike(
            new WriteValueCollection([ScalarValue::localizableValue('my_text_attribute', 'Lorem ipsum', 'en_US')])
        );
    }

    function it_removes_not_granted_values_from_an_entity_with_values_with_variation(
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodes
    ) {
        $familyVariant = new FamilyVariant();
        $entityWithValues = new ProductModel();
        $entityWithValues->setFamilyVariant($familyVariant);
        $entityWithValues->setValues(new WriteValueCollection(
            [
                ScalarValue::localizableValue('granted_attribute', 'some_value', 'en_US'),
                ScalarValue::localizableValue('granted_attribute', 'some_other_value', 'de_DE'),
                OptionValue::localizableValue('non_granted_attribute', 'foo', 'en_US'),
                OptionValue::localizableValue('non_granted_attribute', 'foo', 'de_DE'),
            ]
        ));

        $getViewableAttributeCodes->forAttributeCodes(['granted_attribute', 'non_granted_attribute'], 42)
            ->willReturn(['granted_attribute']);

        $filteredEntity = $this->filter($entityWithValues);
        $filteredEntity->shouldBeAnInstanceOf(EntityWithValuesInterface::class);
        $filteredEntity->getValues()->shouldBeLike(
            new WriteValueCollection([ScalarValue::localizableValue('granted_attribute', 'some_value', 'en_US')])
        );
    }

    function it_throws_an_exception_if_subject_is_not_an_entity_with_values()
    {
        $this->shouldThrow(InvalidObjectException::objectExpected(ClassUtils::getClass(new \stdClass()), EntityWithValuesInterface::class))
            ->during('filter', [new \stdClass()]);
    }

    function it_throws_an_exception_when_no_user_is_authenticated(
        TokenInterface $token
    ) {
        $token->getUser()->willReturn(null);
        $this->shouldThrow(new \RuntimeException('Could not find any authenticated user'))->during('filter', [new Product()]);
    }

    function it_does_not_filter_anything_for_the_system_user(
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodes,
        UserInterface $user,
        WriteValueCollection $values
    ) {
        $user->getId()->willReturn(null);
        $user->getUsername()->willReturn(UserInterface::SYSTEM_USER_NAME);

        $values->getIterator()->willReturn(new \ArrayIterator([]));
        $product = new Product();
        $product->setValues($values->getWrappedObject());

        $getViewableAttributeCodes->forAttributeCodes(Argument::cetera())->shouldNotBeCalled();
        $values->remove(Argument::any())->shouldNotBeCalled();

        $this->filter($product)->shouldBeLike($product);
    }
}
