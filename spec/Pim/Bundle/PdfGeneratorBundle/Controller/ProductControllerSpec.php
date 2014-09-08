<?php

namespace spec\Pim\Bundle\PdfGeneratorBundle\Controller;

use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\PdfGeneratorBundle\Renderer\RendererRegistry;
use Symfony\Component\HttpFoundation\Request;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductControllerSpec extends ObjectBehavior
{
    function let(ProductManager $productManager, RendererRegistry $rendererRegistry)
    {
        $this->beConstructedWith($productManager, $rendererRegistry);
    }

    function it_generates_a_pdf_for_a_given_product(Request $request, AbstractProduct $blender, $rendererRegistry, $productManager)
    {
        $productManager->find(12)->willReturn($blender);

        $request->get('dataLocale', null)->willReturn('fr_FR');
        $request->get('dataScope', null)->willReturn('mobile');

        $now = new \DateTime('now');

        $rendererRegistry->render($blender, 'pdf', [
            'locale'        => 'fr_FR',
            'renderingDate' => $now,
            'scope'         => 'mobile',
        ])->shouldBeCalled();

        $blender->getIdentifier()->shouldBeCalled();

        $this->downloadPdfAction($request, 12);
    }

    function it_throws_an_exception_if_there_is_no_compatible_renderer(Request $request, AbstractProduct $blender, $rendererRegistry, $productManager)
    {
        $productManager->find(12)->willReturn($blender);

        $request->get('dataLocale', null)->willReturn('fr_FR');
        $request->get('dataScope', null)->willReturn('mobile');

        $now = new \DateTime('now');

        $rendererRegistry->render($blender, 'pdf', [
            'locale'        => 'fr_FR',
            'renderingDate' => $now,
            'scope'         => 'mobile',
        ])->willThrow('Pim\Bundle\PdfGeneratorBundle\Exception\RendererRequiredException');

        $this->shouldThrow('Symfony\Component\HttpKernel\Exception\HttpException')->during('downloadPdfAction', [$request, 12]);
    }

    function it_throws_an_exception_if_the_product_doesnt_exist(Request $request, AbstractProduct $blender, $rendererRegistry, $productManager)
    {
        $productManager->find(12)->willReturn(null);

        $this->shouldThrow('Symfony\Component\HttpKernel\Exception\NotFoundHttpException')->during('downloadPdfAction', [$request, 12]);
    }
}
