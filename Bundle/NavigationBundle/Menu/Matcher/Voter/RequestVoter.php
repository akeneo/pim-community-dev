<?php

namespace Oro\Bundle\NavigationBundle\Menu\Matcher\Voter;

use Knp\Menu\Matcher\Voter\UriVoter;
use Symfony\Component\HttpFoundation\Request;

class RequestVoter extends UriVoter
{
    /**
     * @var Request
     */
    private $request;

    public function setRequest(Request $request)
    {
        $this->request = $request;

        $this->setUri($request->getRequestUri());

        return $this;
    }
}
