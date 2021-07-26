<?php

namespace Specification\Akeneo\Pim\Permission\Component\Merger;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Permission\Component\Merger\NotGrantedValuesMerger;
use Akeneo\Pim\Permission\Component\NotGrantedDataMergerInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\GetAllViewableLocalesForUser;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\GetViewableAttributeCodesForUserInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Common\Util\ClassUtils;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class NotGrantedValuesMergerSpec extends ObjectBehavior
{
    function let(
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodes,
        GetAllViewableLocalesForUser $getViewableLocaleCodesForUser,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user
    ) {
        $user->getId()->willReturn(42);
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);
        $getViewableLocaleCodesForUser->fetchAll(42)->willReturn(['en_US']);

        $this->beConstructedWith($getViewableAttributeCodes, $getViewableLocaleCodesForUser, $tokenStorage);
    }

    function it_implements_a_not_granted_data_merger_interface()
    {
        $this->shouldImplement(NotGrantedDataMergerInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NotGrantedValuesMerger::class);
    }

    function it_does_not_merge_values_when_creating_an_entity(
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodes,
        EntityWithValuesInterface $filteredEntity
    ) {
        $getViewableAttributeCodes->forAttributeCodes(Argument::cetera())->shouldNotBeCalled();
        $this->merge($filteredEntity, null)->shouldReturn($filteredEntity);
    }

    function it_merges_values_in_product(GetViewableAttributeCodesForUserInterface $getViewableAttributeCodes)
    {
        $fullProduct = new Product();
        $fullProduct->setValues(new WriteValueCollection(
            [
                ScalarValue::value('123', 'a text'),
                ScalarValue::value('not_granted_for_owner', 'foo'),
                OptionValue::localizableValue('color', 'yellow', 'en_US'),
                OptionValue::localizableValue('color', 'red', 'fr_FR'),
            ]
        ));

        $filteredProduct = new Product();
        $filteredProduct->setValues(new WriteValueCollection(
            [
                ScalarValue::value('not_granted_for_owner', 'bar'),
                OptionValue::localizableValue('color', 'blue', 'en_US'),
            ]
        ));

        $getViewableAttributeCodes->forAttributeCodes(Argument::is(['123', 'not_granted_for_owner', 'color']), 42)->willReturn(['color']);

        $mergedEntity = $this->merge($filteredProduct, $fullProduct);
        $mergedEntity->shouldBeEqualTo($fullProduct);
        $mergedEntity->getValues()->shouldBeLike(new WriteValueCollection(
            [
                ScalarValue::value('123', 'a text'),
                ScalarValue::value('not_granted_for_owner', 'bar'),
                OptionValue::localizableValue('color', 'red', 'fr_FR'),
                OptionValue::localizableValue('color', 'blue', 'en_US'),
            ]
        ));
    }

    function it_throws_an_exception_if_filtered_subject_is_not_an_entity_with_values()
    {
        $this->shouldThrow(InvalidObjectException::objectExpected(ClassUtils::getClass(new \stdClass()), EntityWithValuesInterface::class))
            ->during('merge', [new \stdClass(), new Product()]);
    }

    function it_throws_an_exception_if_full_subject_is_not_an_entity_with_values()
    {
        $this->shouldThrow(InvalidObjectException::objectExpected(ClassUtils::getClass(new \stdClass()), EntityWithValuesInterface::class))
            ->during('merge', [new Product(), new \stdClass()]);
    }

    function it_throws_an_exception_when_no_user_is_authenticated(
        TokenInterface $token
    ) {
        $token->getUser()->willReturn(null);
        $this->shouldThrow(
            new \RuntimeException('Could not find any authenticated user')
        )->during('merge', [new Product(), new Product()]);
    }

    function it_only_takes_the_new_values_for_the_system_user(
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodes,
        UserInterface $user
    ) {
        $user->getId()->willReturn(null);
        $user->getUsername()->willReturn(UserInterface::SYSTEM_USER_NAME);

        $filteredProduct = (new Product())->setIdentifier('filtered');
        $newValues = new WriteValueCollection(
            [
                ScalarValue::value('a_test', 'foo'),
                ScalarValue::value('a_boolean', true),
            ]
        );
        $filteredProduct->setValues($newValues);

        $fullEntity = (new Product())->setIdentifier('full');
        $fullEntity->setValues(new WriteValueCollection(
            [
                OptionValue::value('color', 'red'),
                OptionValue::value('size', 'XXL'),
            ]
        ));

        $getViewableAttributeCodes->forAttributeCodes(Argument::cetera())->shouldNotBeCalled();

        $result = $this->merge($filteredProduct, $fullEntity);
        $result->shouldBeEqualTo($fullEntity);
        $result->getValues()->shouldBeLike($newValues);
    }
}
