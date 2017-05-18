<?php

namespace Pim\Bundle\ApiBundle\Negotiator;

use FOS\RestBundle\Util\StopFormatListenerException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Content type negotiator aims to get the allowed content types for a given request.
 *
 * FosRestBundle allows to provide the best accept type given a request.
 * The goal of this interface is to do the same thing for the content types.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ContentTypeNegotiatorInterface
{
    /**
     * Returns the content types allowed for a given request.
     *
     * @param Request $request
     *
     * @throws StopFormatListenerException
     *
     * @return string[] array of content types
     */
    public function getContentTypes(Request $request);
}
