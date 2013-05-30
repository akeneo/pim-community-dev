<?php

namespace Pim\Bundle\NavigationBundle\Menu;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RequestVoter implements VoterInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function matchItem(ItemInterface $item)
    {
        $requestUri = $this->container->get('request')->getRequestUri();
        $itemUri    = $item->getUri();

        if ($itemUri === $requestUri || strpos($requestUri, $itemUri) === 0) {
            return true;
        }

        return null;
    }
}
