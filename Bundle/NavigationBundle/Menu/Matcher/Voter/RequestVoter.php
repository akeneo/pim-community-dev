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

        // PIM-3695 : bump knp menu version to 2.0.0, remove the call to a deprecated method
        // $this->setUri($request->getRequestUri());

        return $this;
    }
}
