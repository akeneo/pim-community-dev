<?php

namespace Oro\Bundle\NavigationBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\Rest\Util\Codes;

use Oro\Bundle\NavigationBundle\Provider\BuilderChainProvider;
use Knp\Menu\Iterator\RecursiveItemIterator;
use Knp\Menu\ItemInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @RouteResource("shortcuts")
 * @NamePrefix("oro_api_")
 */
class ShortcutsController extends FOSRestController
{
    /**
     * REST GET list
     *
     * @param string $query
     *
     * @ApiDoc(
     *  description="Get all shortcuts items for user",
     *  resource=true
     * )
     * @return Response
     */
    public function getAction($query)
    {
        /** @var $provider BuilderChainProvider */
        $provider = $this->container->get('oro_menu.builder_chain');
        /** @var $translator TranslatorInterface */
        $translator = $this->get('translator');
        $result = array();
        $items = $provider->get('shortcuts');
        /** @var $item ItemInterface */
        $itemIterator = new RecursiveItemIterator($items);
        $iterator = new \RecursiveIteratorIterator($itemIterator, \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $item) {
            if ($item->getExtra('isAllowed')) {
                $key = $translator->trans($item->getLabel());
                if (strpos(strtolower($key), strtolower($query)) !== false) {
                    $result[$key] = array('url' => $item->getUri());
                }
            }
        }

        return $this->handleView(
            $this->view($result, is_array($result) ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND)
        );
    }
}
