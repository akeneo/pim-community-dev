<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Form\Type\MassEditAction;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager;
use Prophecy\Argument;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Test\FormInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ClassifyTypeSpec extends ObjectBehavior
{
    function let(
        CategoryRepositoryInterface $categoryRepository,
        CategoryManager $categoryManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->beConstructedWith(
            $categoryRepository,
            $categoryManager,
            $tokenStorage,
            'PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation\Classify'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\EnrichBundle\Form\Type\MassEditAction\ClassifyType');
    }

    function it_is_a_classify_form_type()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\Form\Type\MassEditAction\ClassifyType');
    }

    function it_builds_view(
        $tokenStorage,
        $categoryManager,
        TokenInterface $token,
        UserInterface $user,
        FormView $view,
        FormInterface $form
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $categoryManager->getAccessibleTrees($user)->shouldBeCalled();

        $this->buildView($view, $form, []);
    }
}
