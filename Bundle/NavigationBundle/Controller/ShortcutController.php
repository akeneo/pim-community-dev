<?php

namespace Oro\Bundle\NavigationBundle\Controller;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Knp\Menu\Iterator\RecursiveItemIterator;
use Knp\Menu\ItemInterface;

use Oro\Bundle\NavigationBundle\Provider\BuilderChainProvider;

/**
 * @Route("/shortcut")
 */
class ShortcutController extends Controller
{
    /**
     * @Route("actionslist", name="oro_shortcut_actionslist")
     * @Template
     */
    public function actionslistAction()
    {
        $result = array();

        /** @var $provider BuilderChainProvider */
        $provider = $this->container->get('oro_menu.builder_chain');
        /** @var $translator TranslatorInterface */
        $translator = $this->get('translator');
        $items = $provider->get('shortcuts');
        /** @var $item ItemInterface */
        $itemIterator = new RecursiveItemIterator($items);
        $iterator = new \RecursiveIteratorIterator($itemIterator, \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $item) {
            if ($item->getExtra('isAllowed')) {
                $key = $translator->trans($item->getLabel());
                $result[$key] = array('url' => $item->getUri(), 'description' => $item->getExtra('description'));
            }
        }

        return array(
            'actionsList'  => $result,
        );
    }
}
