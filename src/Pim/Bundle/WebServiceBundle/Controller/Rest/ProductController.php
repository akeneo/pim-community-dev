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
        $userContext = $this->get('pim_user.context.user');
        $channels    = array_keys($userContext->getChannelChoicesWithUserChannel());
        $locales     = $userContext->getUserLocaleCodes();

        $channelCodes = $request->get('channels', $request->get('channel', []));
        if ($channelCodes) {
            $channelCodes = explode(',', $channelCodes);
        }

        foreach ($channels as $index => $channelCode) {
            if (!in_array($channelCode, $channelCodes)) {
                unset($channels[$index]);
            }
        }

        $localeCodes = $request->get('locales', $request->get('locale', []));
        if ($localeCodes) {
            $localeCodes = explode(',', $localeCodes);
        }

        foreach ($locales as $index => $localeCode) {
            if (!in_array($localeCode, $localeCodes)) {
                unset($locales[$index]);
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
