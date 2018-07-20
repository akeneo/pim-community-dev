<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\Api;

use Akeneo\Pim\Permission\Component\Exception\ResourceAccessDeniedException;
use Akeneo\Pim\Permission\Component\Exception\ResourceViewAccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

/**
 * Returns the good message if a resource is not granted
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    /** @var AccessDeniedHandlerInterface */
    private $accessDeniedHandler;

    /**
     * @param AccessDeniedHandlerInterface $accessDeniedHandler
     */
    public function __construct(AccessDeniedHandlerInterface $accessDeniedHandler)
    {
        $this->accessDeniedHandler = $accessDeniedHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, AccessDeniedException $accessDeniedException): Response
    {
        if ($accessDeniedException instanceof ResourceAccessDeniedException) {
            $responseCode = $accessDeniedException instanceof ResourceViewAccessDeniedException
                ? Response::HTTP_NOT_FOUND
                : Response::HTTP_FORBIDDEN;

            $response = new Response(
                json_encode(
                    [
                        'code'    => $responseCode,
                        'message' => $accessDeniedException->getMessage(),
                    ]
                ),
                $responseCode
            );

            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        return $this->accessDeniedHandler->handle($request, $accessDeniedException);
    }
}
