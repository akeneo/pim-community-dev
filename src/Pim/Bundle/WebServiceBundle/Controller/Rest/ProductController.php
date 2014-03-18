<?php

namespace Pim\Bundle\WebServiceBundle\Controller\Rest;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

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
                    return new Response('', 403);
                }
            }
        } else {
            $channels = $availableChannels;
        }

        $locales = $request->get('locales', $request->get('locale', null));
        if ($locales !== null) {
            $locales = explode(',', $locales);

            foreach ($locales as $locale) {
                if (!in_array($locale, $availableLocales)) {
                    return new Response('', 403);
                }
            }
        } else {
            $locales = $availableLocales;
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
        $manager = $this->get('pim_catalog.manager.product');
        $product = $manager->findByIdentifier($identifier);

        if (!$product) {
            return new Response('', 404);
        }

        $serializer = $this->get('pim_serializer');
        $data = $serializer->serialize(
            $product,
            'json',
            [
                'locales' => $locales,
                'channels' => $channels,
                'resource' => $this->generateUrl(
                    'oro_api_get_product',
                    array(
                        'identifier' => $product->getIdentifier()->getData()
                    ),
                    true
                )
            ]
        );

        return new Response($data);
    }
}
