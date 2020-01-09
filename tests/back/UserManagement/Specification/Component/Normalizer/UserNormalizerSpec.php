<?php

namespace Specification\Akeneo\UserManagement\Component\Normalizer;

use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Tool\Component\Classification\Model\Category;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Normalizer\UserNormalizer;
use Oro\Bundle\PimDataGridBundle\Repository\DatagridViewRepositoryInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserNormalizerSpec extends ObjectBehavior
{
    function let(
        DateTimeNormalizer $dateTimeNormalizer,
        NormalizerInterface $fileNormalizer,
        SecurityFacade $securityFacade,
        TokenStorageInterface $tokenStorage,
        DatagridViewRepositoryInterface $datagridViewRepo,
        NormalizerInterface $normalizerOne,
        NormalizerInterface $normalizerTwo
    )
    {
        $this->beConstructedWith(
            $dateTimeNormalizer,
            $fileNormalizer,
            $securityFacade,
            $tokenStorage,
            $datagridViewRepo,
            [$normalizerOne, $normalizerTwo],
            'property_name'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UserNormalizer::class);
    }

    function it_is_user_normalizer($datagridViewRepo, $normalizerOne, $normalizerTwo)
    {
        $user = new User();
        $user->setCatalogLocale(new Locale());
        $user->setUiLocale(new Locale());
        $user->setCatalogScope(new Channel());
        $user->setDefaultTree(new Category());
        $user->addProperty('property_name', 'value');

        $datagridViewRepo->getDatagridViewTypeByUser($user)->willReturn([]);

        $result = [
            'code'                      => null,
            'enabled'                   => true,
            'username'                  => null,
            'email'                     => null,
            'name_prefix'               => null,
            'first_name'                => null,
            'middle_name'               => null,
            'last_name'                 => null,
            'name_suffix'               => null,
            'phone'                     => null,
            'image'                     => null,
            'last_login'                => null,
            'login_count'               => 0,
            'catalog_default_locale'    => null,
            'user_default_locale'       => null,
            'catalog_default_scope'     => null,
            'default_category_tree'     => null,
            'email_notifications'       => false,
            'timezone'                  => 'UTC',
            'groups'                    => [],
            'roles'                     => [],
            'product_grid_filters'      => [],
            'avatar'                    => [
                'filePath'         => null,
                'originalFilename' => null,
            ],
            'meta'                      => [
                'id'      => null,
                'created' => null,
                'updated' => null,
                'form'    => 'pim-user-show',
                'image'   => [
                    'filePath' => null
                ]
            ],
            'properties' => [
                'property_name' => 'value',
                'property_one' => 'valueOne'
            ],
            'property_two' => 'valueTwo',
        ];

        $normalizerOne->normalize($user, Argument::cetera())->willReturn(['properties' => ['property_one' => 'valueOne']]);
        $normalizerTwo->normalize($user, Argument::cetera())->willReturn(['property_two' => 'valueTwo']);


        $this->normalize($user)->shouldReturn($result);
    }

    function it_provides_the_edit_user_form_meta_if_user_has_edit_users_permission(
        $datagridViewRepo,
        $securityFacade,
        $normalizerOne,
        $normalizerTwo
    ) {
        $user = new User();
        $user->setCatalogLocale(new Locale());
        $user->setUiLocale(new Locale());
        $user->setCatalogScope(new Channel());
        $user->setDefaultTree(new Category());
        $user->addProperty('property_name', 'value');

        $normalizerOne->normalize($user, Argument::cetera())->willReturn(['properties' => []]);
        $normalizerTwo->normalize($user, Argument::cetera())->willReturn([]);

        $datagridViewRepo->getDatagridViewTypeByUser($user)->willReturn([]);

        $securityFacade->isGranted('pim_user_user_edit')->willReturn(true);

        $normalized = $this->normalize($user);
        $normalized->shouldHaveKey('meta');
        $normalized['meta']->shouldHaveKeyWithValue('form', 'pim-user-edit-form');
    }

    function it_provides_the_edit_profile_form_meta_if_current_user_is_the_same_as_the_normalized_one(
        $datagridViewRepo,
        $normalizerOne,
        $normalizerTwo,
        $securityFacade,
        $tokenStorage,
        TokenInterface $token,
        UserInterface $currentUser
    ) {
        $user = new User();
        $user->setId(42);
        $user->setCatalogLocale(new Locale());
        $user->setUiLocale(new Locale());
        $user->setCatalogScope(new Channel());
        $user->setDefaultTree(new Category());
        $user->addProperty('property_name', 'value');

        $normalizerOne->normalize($user, Argument::cetera())->willReturn(['properties' => []]);
        $normalizerTwo->normalize($user, Argument::cetera())->willReturn([]);

        $datagridViewRepo->getDatagridViewTypeByUser($user)->willReturn([]);

        $securityFacade->isGranted('pim_user_user_edit')->willReturn(false);

        $currentUser->getId()->willReturn(42);
        $token->getUser()->willReturn($currentUser);
        $tokenStorage->getToken()->willReturn($token);

        $normalized = $this->normalize($user);
        $normalized->shouldHaveKey('meta');
        $normalized['meta']->shouldHaveKeyWithValue('id', 42);
        $normalized['meta']->shouldHaveKeyWithValue('form', 'pim-user-profile-form');
    }
}
