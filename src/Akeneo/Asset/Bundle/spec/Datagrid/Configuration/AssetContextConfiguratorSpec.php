<?php

namespace spec\Akeneo\Asset\Bundle\Datagrid\Configuration;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Model\Locale;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Pim\Bundle\DataGridBundle\Datagrid\Request\RequestParametersExtractorInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\Pim\Permission\Bundle\User\UserContext;

class AssetContextConfiguratorSpec extends ObjectBehavior
{
    function let(
        DatagridConfiguration $configuration,
        RequestParametersExtractorInterface $paramsExtractor,
        UserContext $userContext
    ) {
        $this->beConstructedWith($paramsExtractor, $userContext);
    }

    function it_is_a_configurator()
    {
        $this->shouldImplement('Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface');
    }

    function it_returns_locale_from_request($configuration, $paramsExtractor)
    {
        $paramsExtractor->getParameter('dataLocale')->willReturn('fr_FR');
        $configuration->offsetSetByPath(sprintf('[source][%s]', ConfiguratorInterface::DISPLAYED_LOCALE_KEY), 'fr_FR')
            ->shouldBeCalled();
        $this->configure($configuration);
    }

    function it_returns_default_user_locale_from_catalog(
        $configuration,
        $userContext,
        $paramsExtractor,
        UserInterface $user,
        Locale $locale
    ) {
        $paramsExtractor->getParameter('dataLocale')
            ->willThrow(new \LogicException('Parameter "dataLocale" is expected'));
        $locale->getCode()->willReturn('fr_FR');
        $user->getCatalogLocale()->willReturn($locale);
        $userContext->getUser()->willReturn($user);
        $configuration->offsetSetByPath(sprintf('[source][%s]', ConfiguratorInterface::DISPLAYED_LOCALE_KEY), 'fr_FR')
            ->shouldBeCalled();
        $this->configure($configuration);
    }
}
