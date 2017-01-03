<?php

namespace spec\Pim\Bundle\DashboardBundle\Widget;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\ImportExportBundle\Manager\JobExecutionManager;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Akeneo\Component\Localization\Presenter\PresenterInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Translation\TranslatorInterface;

class LastOperationsWidgetSpec extends ObjectBehavior
{
    function let(
        JobExecutionManager $manager,
        TranslatorInterface $translator,
        PresenterInterface $presenter,
        TokenStorageInterface $tokenStorage
    ) {
        $this->beConstructedWith($manager, $translator, $presenter, $tokenStorage);
    }

    function it_is_a_widget()
    {
        $this->shouldImplement('Pim\Bundle\DashboardBundle\Widget\WidgetInterface');
    }

    function it_has_an_alias()
    {
        $this->getAlias()->shouldReturn('last_operations');
    }

    function it_exposes_the_last_operations_widget_template()
    {
        $this->getTemplate()->shouldReturn('PimDashboardBundle:Widget:last_operations.html.twig');
    }

    function it_has_no_template_parameters()
    {
        $this->getParameters()->shouldReturn([]);
    }

    function it_exposes_the_last_operations_data(
        $manager,
        $translator,
        $presenter,
        $tokenStorage,
        TokenInterface $token,
        LocaleInterface $locale,
        UserInterface $user
    ) {
        $date = new \DateTime('2015-12-01');
        $operation = [
            'date'   => $date,
            'type'   => 'import',
            'label'  => 'My import',
            'status' => 1,
            'id'     => 3
        ];

        $manager->getLastOperationsData(Argument::type('array'))->willReturn([$operation]);

        $translator->trans('pim_import_export.batch_status.' . $operation['status'])->willReturn('Completed');
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getUiLocale()->willReturn($locale);
        $locale->getCode()->willReturn('fr_FR');
        $presenter->present($date, ['locale' => 'fr_FR'])->willReturn('01/12/2015');

        $operation['statusLabel'] = 'Completed';
        $operation['date'] = '01/12/2015';
        $this->getData()->shouldReturn([$operation]);
    }
}
