<?php

namespace spec\Pim\Bundle\PdfGeneratorBundle\Controller;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\PdfGeneratorBundle\Renderer\RendererRegistry;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;

class ProductControllerSpec extends ObjectBehavior
{
    function let(ProductRepositoryInterface $productRepository, RendererRegistry $rendererRegistry)
    {
        $this->beConstructedWith($productRepository, $rendererRegistry);
    }

    function it_generates_a_pdf_for_a_given_product(
        Request $request,
        ProductInterface $blender,
        $rendererRegistry,
        $productRepository
    ) {
        $productRepository->find(12)->willReturn($blender);

        $request->get('dataLocale', null)->willReturn('fr_FR');
        $request->get('dataScope', null)->willReturn('mobile');

        $now = new \DateTime('now');

        $rendererRegistry->render($blender, 'pdf', Argument::type('array'))->shouldBeCalled();

        $blender->getIdentifier()->shouldBeCalled();

        $this->downloadPdfAction($request, 12);
    }

    function it_throws_an_exception_if_there_is_no_compatible_renderer(
        Request $request,
        ProductInterface $blender,
        $rendererRegistry,
        $productRepository
    ) {
        $productRepository->find(12)->willReturn($blender);

        $request->get('dataLocale', null)->willReturn('fr_FR');
        $request->get('dataScope', null)->willReturn('mobile');

        $now = new \DateTime('now');

        $rendererRegistry
            ->render($blender, 'pdf', Argument::type('array'))
            ->willThrow('Pim\Bundle\PdfGeneratorBundle\Exception\RendererRequiredException');

        $this
            ->shouldThrow('Symfony\Component\HttpKernel\Exception\HttpException')
            ->during('downloadPdfAction', [$request, 12]);
    }

    function it_throws_an_exception_if_the_product_doesnt_exist(
        Request $request,
        ProductInterface $blender,
        $rendererRegistry,
        $productRepository
    ) {
        $productRepository->find(12)->willReturn(null);

        $this
            ->shouldThrow('Symfony\Component\HttpKernel\Exception\NotFoundHttpException')
            ->during('downloadPdfAction', [$request, 12]);
    }
}
