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
 * @RouteResource("product")
 * @NamePrefix("oro_api_")
 */
class ProductController extends FOSRestController
{
    /**
     * Get a single product
     *
     * @param Request $request
     * @param string  $identifier
     *
     * @ApiDoc(
     *      description="Get a single product",
     *      resource=true
     * )
     *
     * @return Response
     */
    public function getAction(Request $request, $identifier)
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

        return $this->handleGetRequest($identifier, $channels, $locales);
    }

    /**
     * Return a single product
     *
     * @param string   $identifier
     * @param string[] $channels
     * @param string[] $locales
     *
     * @return Response
     */
    protected function handleGetRequest($identifier, $channels, $locales)
    {
        /* Handle requests for skus that contain a period
	    * cURL cannot handle API requests that contain a period
	    * but we can substitute with hexidecimal in the cURL request
            * and then replace it here.
	    * Using additional leading and trailing underscore to limit the
	    * possibility of conflict with a legitimate sku.
	    */
	$identifier = str_replace('_0x2E_', '.', $identifier);	
        
        $manager = $this->get('pim_catalog.manager.product');
        $product = $manager->findByIdentifier($identifier);

        if (!$product) {
            return new Response(sprintf('Product "%s" not found', $identifier), 404);
        }

        try {
            $serializedData = $this->serializeProduct($product, $channels, $locales);
        } catch (AccessDeniedException $exception) {
            return new Response(sprintf('Access denied to the product "%s"', $product->getIdentifier()), 403);
        }

        return new Response($serializedData);
    }

    /**
     * Serialize a single product
     *
     * @param ProductInterface $product
     * @param string[]         $channels
     * @param string[]         $locales
     *
     * @return array
     */
    protected function serializeProduct(ProductInterface $product, $channels, $locales)
    {
        $url = $this->generateUrl(
            'oro_api_get_product',
            array(
                'identifier' => $product->getIdentifier()->getData()
            ),
            true
        );
        $handler = $this->get('pim_webservice.handler.rest.product');
        $data = $handler->get($product, $channels, $locales, $url);

        return $data;
    }
}
