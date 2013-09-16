<?php

namespace Oro\Bundle\NavigationBundle\Controller;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Knp\Menu\Iterator\RecursiveItemIterator;
use Knp\Menu\ItemInterface;

use Oro\Bundle\NavigationBundle\Provider\BuilderChainProvider;
use Oro\Bundle\UserBundle\Annotation\Acl;

/**
 * @Route("/shortcut")
 *
 * @Acl(
 *     id="oro_shortcut",
 *     name="Get windows state",
 *     description="Get windows state",
 *     parent="root"
 * )
 */
class ShortcutController extends Controller
{
    protected $uris = array();

    /**
     * @Route("actionslist", name="oro_shortcut_actionslist")
     * @Template
     *
     * @Acl(
     *     id="oro_shortcut_actions_list",
     *     name="List shortcuts",
     *     description="List shortcuts",
     *     parent="oro_shortcut"
     * )
     */
    public function actionslistAction()
    {
        /** @var $provider BuilderChainProvider */
        $provider = $this->container->get('oro_menu.builder_chain');
        /**
         * merging shortcuts and application menu
         */
        $shortcuts = $provider->get('shortcuts');
        $menuItems = $provider->get('application_menu');
        $result = array_merge($this->getResults($shortcuts), $this->getResults($menuItems));
        ksort($result);

        return array(
            'actionsList'  => $result,
        );
    }

    /**
     * @param ItemInterface $items
     * @return array
     */
    protected function getResults(ItemInterface $items)
    {
        /** @var $translator TranslatorInterface */
        $translator = $this->get('translator');
        $itemIterator = new RecursiveItemIterator($items);
        $iterator = new \RecursiveIteratorIterator($itemIterator, \RecursiveIteratorIterator::SELF_FIRST);
        /** @var $item ItemInterface */
        foreach ($iterator as $item) {
            if ($item->getExtra('isAllowed') && !in_array($item->getUri(), $this->uris) && $item->getUri() !== '#') {
                $key = $translator->trans($item->getLabel());
                $result[$key] = array('url' => $item->getUri(), 'description' => $item->getExtra('description'));
                $this->uris[] = $item->getUri();
            }
        }

        return $result;
    }
}
