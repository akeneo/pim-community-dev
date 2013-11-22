<?php

namespace Oro\Bundle\SecurityBundle\Request\ParamConverter;

use Oro\Bundle\SecurityBundle\SecurityFacade;

use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter as BaseParamConverter;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class DoctrineParamConverter
 * @package Oro\Bundle\SecurityBundle\Request\ParamConverter
 */
class DoctrineParamConverter extends BaseParamConverter
{
    /**
     * @var SecurityFacade
     */
    protected $securityFacade;

    /**
     * @param ManagerRegistry $registry
     * @param SecurityFacade $securityFacade
     */
    public function __construct(ManagerRegistry $registry = null, SecurityFacade $securityFacade = null)
    {
        parent::__construct($registry);
        $this->securityFacade = $securityFacade;
    }

    /**
     * Stores the object in the request.
     *
     * @param Request $request
     * @param ConfigurationInterface $configuration
     * @return bool
     * @throws AccessDeniedException When User doesn't have permission to the object
     * @throws NotFoundHttpException When object not found
     * @throws \LogicException       When unable to guess how to get a Doctrine instance from the request information
     */
    public function apply(Request $request, ConfigurationInterface $configuration)
    {
        $request->attributes->set('_oro_access_checked', false);
        $isSet = parent::apply($request, $configuration);

        if ($isSet) {
            $object = $request->attributes->get($configuration->getName());
            $controller = $request->attributes->get('_controller');
            if ($object && strpos($controller, '::') !== false) {
                $controllerData = explode('::', $controller);
                $permission = $this->securityFacade->getClassMethodAnnotationPermission($controllerData[0], $controllerData[1]);

                if (!$this->securityFacade->isGranted($permission, $object)) {
                    throw new AccessDeniedException('You do not get ' . $permission . ' permission for this object');
                } else {
                    $request->attributes->set('_oro_access_checked', true);
                }
            }
        }

        return $isSet;
    }
}
