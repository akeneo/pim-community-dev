<?php

namespace Pim\Bundle\UserBundle\Controller;

use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * User rest controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserRestController
{
    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param SecurityContextInterface $securityContext
     * @param NormalizerInterface      $normalizer
     */
    public function __construct(SecurityContextInterface $securityContext, NormalizerInterface $normalizer)
    {
        $this->securityContext = $securityContext;
        $this->normalizer      = $normalizer;
    }

    /**
     * @return JsonResponse
     */
    public function getAction()
    {
        $token = $this->securityContext->getToken();
        $user = null !== $token ? $token->getUser() : null;

        if (null === $user) {
            throw new NotFoundHttpException('No logged in user found');
        }

        return new JsonResponse($this->normalizer->normalize($user, 'internal_api'));
    }
}
