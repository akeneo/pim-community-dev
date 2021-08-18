<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Query\Structure;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\FindFlattenAttributesInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\FlattenAttribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\GetViewableAttributeCodesForUserInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\Attribute\Attribute;
use Akeneo\Platform\TailoredExport\Domain\Query\Attribute\ViewableAttributesResult;
use Akeneo\Platform\TailoredExport\Infrastructure\Query\Structure\FindViewableAttributes;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class FindViewableAttributesSpec extends ObjectBehavior
{
    public function let(
        FindFlattenAttributesInterface $findFlattenAttributes,
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodesForUser,
        TokenStorageInterface $tokenStorage
    ): void {
        $this->beConstructedWith($findFlattenAttributes, $getViewableAttributeCodesForUser, $tokenStorage);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(FindViewableAttributes::class);
    }

    public function it_searches_for_multiple_viewable_attributes(
        FindFlattenAttributesInterface $findFlattenAttributes,
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodesForUser,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user
    ) {
        $localeCode = 'fr_FR';
        $limit = 10;
        $offset = 0;
        $search = 'my_search';
        $attributeTypes = ['string', 'number'];

        $viewableAttributeCode = 'attribute';
        $unviewableAttributeCode = 'unknown_attribute_code';
        $attributeLabel = 'attribute_label';
        $attributeGroupCode = 'attribute_group';
        $attributeGroupLabel = 'attribute_group_label';

        $userId = 1;
        $user->getId()->willReturn($userId);
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $flattenAttributes = [
            new FlattenAttribute(
                $viewableAttributeCode, $attributeLabel,
                $attributeGroupCode, $attributeGroupLabel
            ),
            new FlattenAttribute(
                $unviewableAttributeCode, $attributeLabel, $attributeGroupCode, $attributeGroupLabel
            ),
        ];
        $findFlattenAttributes->execute($localeCode, $limit, $attributeTypes, $offset, $search)
            ->willReturn($flattenAttributes);

        $updatedOffset = $offset + \count($flattenAttributes);
        $findFlattenAttributes->execute($localeCode, $limit, $attributeTypes, $updatedOffset, $search)
            ->willReturn([]);

        $getViewableAttributeCodesForUser->forAttributeCodes(
            [$viewableAttributeCode, $unviewableAttributeCode],
            $userId
        )->willReturn([$viewableAttributeCode]);

        $this->execute($localeCode, $limit, $attributeTypes, $offset, $search)->shouldBeLike(
            new ViewableAttributesResult(
                $updatedOffset,
                [new Attribute($viewableAttributeCode, $attributeLabel, $attributeGroupCode, $attributeGroupLabel)]
            )
        );
    }
}
