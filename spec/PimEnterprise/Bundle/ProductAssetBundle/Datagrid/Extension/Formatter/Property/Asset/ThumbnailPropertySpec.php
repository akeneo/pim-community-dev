<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\Datagrid\Extension\Formatter\Property\Asset;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyConfiguration;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Pim\Bundle\DataGridBundle\Datagrid\Request\RequestParametersExtractorInterface;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;

class ThumbnailPropertySpec extends ObjectBehavior
{
    function let(
        \Twig_Environment $environment,
        RequestParametersExtractorInterface $paramsExtractor,
        UserContext $userContext,
        LocaleRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository
    ) {
        $this->beConstructedWith($environment, $paramsExtractor, $userContext, $localeRepository, $channelRepository);

        $params = new PropertyConfigurationFake(['template' => 'my-template']);
        $this->init($params);
    }

    function it_is_a_property()
    {
        $this->shouldImplement('Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface');
    }

    function it_returns_the_template_with(
        $localeRepository,
        $paramsExtractor,
        $channelRepository,
        $environment,
        ResultRecordInterface $record,
        LocaleInterface $localeEN,
        ChannelInterface $channelMobile,
        AssetInterface $asset,
        FileInfoInterface $fileInfo,
        \Twig_TemplateInterface $template
    ) {
        $paramsExtractor->getParameter('dataLocale')->willReturn('en_US');
        $paramsExtractor->getDatagridParameter('_filter')->willReturn(['scope' => ['value' => 'mobile']]);

        $localeRepository->findOneByIdentifier('en_US')->willReturn($localeEN);
        $channelRepository->findOneByIdentifier('mobile')->willReturn($channelMobile);

        $record->getRootEntity()->willReturn($asset);

        $asset->getFileForContext($channelMobile, $localeEN)->willReturn($fileInfo);
        $fileInfo->getKey()->willReturn('a/b/c/d/abcdmyimage.jpg');

        $environment->loadTemplate('my-template')->willReturn($template);
        $template->render(['path' => 'a/b/c/d/abcdmyimage.jpg'])->willReturn('<div>My Template !</div>');

        $this->getValue($record)->shouldReturn('<div>My Template !</div>');
    }
}

class PropertyConfigurationFake extends PropertyConfiguration
{
    public function __construct(array $params)
    {
        $this->params = $params;
    }
}
