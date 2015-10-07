<?php

namespace Oro\Bundle\NavigationBundle\Menu\Matcher\Voter;

use Symfony\Component\HttpFoundation\Request;

use Knp\Menu\Matcher\Voter\VoterInterface;
use Knp\Menu\ItemInterface;

class RoutePatternVoter implements VoterInterface
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param ItemInterface $item
     * @return bool|null
     */
    public function matchItem(ItemInterface $item)
    {
        if (null === $this->request) {
            return null;
        }

        $route = $this->request->attributes->get('_route');
        if (null === $route) {
            return null;
        }

        $routes = (array) $item->getExtra('routes', array());
        $parameters = (array) $item->getExtra('routesParameters', array());
        foreach ($routes as $testedRoute) {
            if (!$this->routeMatch($testedRoute, $route)) {
                continue;
            }

            if (isset($parameters[$testedRoute]) && !$this->parametersMatch($parameters[$testedRoute])) {
                return null;
            }

            return true;
        }

        return null;
    }

    /**
     * Returns TRUE if route matches pattern.
     *
     * Pattern could be:
     *   - full route name - "oro_user_create"
     *   - a regular expression string - "/^oro_user_\w+$/"
     *   - a string with asterisks - "oro_user_*"
     *
     * @param string $pattern
     * @param string $actualRoute
     * @return boolean
     */
    protected function routeMatch($pattern, $actualRoute)
    {
        if ($pattern == $actualRoute) {
            return true;
        } elseif (0 === strpos($pattern, '/') && strlen($pattern) - 1 === strrpos($pattern, '/')) {
            return preg_match($pattern, $actualRoute);
        } elseif (false !== strpos($pattern, '*')) {
            $pattern = sprintf('/^%s$/', str_replace('*', '\w+', $pattern));
            return preg_match($pattern, $actualRoute);
        } else {
            return false;
        }
    }

    /**
     * Returns TRUE if request matches parameters
     *
     * @param array $parameters
     * @return bool
     */
    protected function parametersMatch(array $parameters)
    {
        foreach ($parameters as $name => $value) {
            if ($this->request->attributes->get($name) != $value) {
                return false;
            }
        }
        return true;
    }
}
