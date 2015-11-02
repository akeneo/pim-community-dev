<?php

namespace Pim\Bundle\WebServiceBundle\Controller\Rest;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Product API controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @RouteResource("products")
 * @NamePrefix("oro_api_")
 */
class ProductsController extends FOSRestController
{
    /**
     * Get products
     *
     * @param Request $request
     *
     * @ApiDoc(
     *      description="Get a single product",
     *      resource=true
     * )
     *
     * @return Response
     */
    public function getAction(Request $request)
    {
        $userContext       = $this->get('pim_user.context.user');
        $availableChannels = array_keys($userContext->getChannelChoicesWithUserChannel());
        $availableLocales  = $userContext->getUserLocaleCodes();

        $channels = $request->get('channels', $request->get('channel', null));
        if ($channels !== null) {
            $channels = explode(',', $channels);

            foreach ($channels as $channel) {
                if (!in_array($channel, $availableChannels)) {
                    return new Response(sprintf('Channel "%s" does not exist or is not available', $channel), 403);
                }
            }
        }

        $locales = $request->get('locales', $request->get('locale', null));
        if ($locales !== null) {
            $locales = explode(',', $locales);

            foreach ($locales as $locale) {
                if (!in_array($locale, $availableLocales)) {
                    return new Response(sprintf('Locale "%s" does not exist or is not available', $locale), 403);
                }
            }
        }

        return $this->handleGetRequest($channels, $locales);
    }

    /**
     * Return a single product
     *
     * @param string[] $channels
     * @param string[] $locales
     *
     * @return Response
     */
    protected function handleGetRequest($channels, $locales)
    {
        $productRepository = $this->container->get('pim_catalog.repository.product');
        $products = $productRepository->findAll();

        try {
            $serializedData = $this->serializeProducts($products, $channels, $locales);
        } catch (AccessDeniedException $exception) {
            return new Response(sprintf('Access denied to the products'), 403);
        }

        return new Response($serializedData);
    }

    /**
     * Serialize many products
     *
     * @param array            $products
     * @param string[]         $channels
     * @param string[]         $locales
     *
     * @return array
     */
    protected function serializeProducts(array $products, $channels, $locales)
    {
        $handler = $this->container->get('pim_webservice.handler.rest.product');
        $data = array();

        foreach($products as &$product) {

            $url = $this->generateUrl(
                'oro_api_get_product',
                array(
                    'identifier' => $product->getIdentifier()->getData()
                ),
                true
            );

            array_push($data, $handler->get($product, $channels, $locales, $url));
        }
        return "[" . implode(",", $data) . "]";
    }
}
