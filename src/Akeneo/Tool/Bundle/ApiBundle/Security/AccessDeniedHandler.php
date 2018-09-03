<?php

namespace Akeneo\Tool\Bundle\ApiBundle\Security;

use Doctrine\Common\Inflector\Inflector;
use Oro\Bundle\SecurityBundle\Exception\AccessDeniedException as OroAccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

/**
 * Handler responsible for returning a response with accurate error message when the user doesn't have the permission
 * to access certain parts of the API.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, AccessDeniedException $exception)
    {
        return new JsonResponse(
            [
                'code'    => Response::HTTP_FORBIDDEN,
                'message' => $this->getMessage($request, $exception)
            ],
            Response::HTTP_FORBIDDEN
        );
    }

    protected function getMessage(Request $request, AccessDeniedException $exception)
    {
        if ($exception instanceof OroAccessDeniedException) {
            $actionName = 'GET' === $request->getMethod() ? 'list' : 'create or update';

            preg_match('`\\\\(\w+)Controller`', $exception->getControllerClass(), $matches);
            $entityName = str_replace('_', ' ', Inflector::tableize(Inflector::pluralize($matches[1])));

            return sprintf('Access forbidden. You are not allowed to %s %s.', $actionName, $entityName);
        }

        return 'You are not allowed to access the web API.';
    }
}
