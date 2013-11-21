<?php

namespace Pim\Bundle\CustomEntityBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Pim\Bundle\CustomEntityBundle\Configuration\Registry;

/**
 * Controller for custom entities
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Controller
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Registry
     */
    protected $configurationRegistry;

    /**
     * Constructor
     *
     * @param Request  $request
     * @param Registry $configurationRegistry
     */
    public function __construct(Request $request, Registry $configurationRegistry)
    {
        $this->request = $request;
        $this->configurationRegistry = $configurationRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function action($customEntityName, $actionName)
    {
        if (!$this->configurationRegistry->has($customEntityName)) {
            throw new NotFoundHttpException;
        }

        $configuration = $this->configurationRegistry->get($customEntityName);

        return call_user_func(
            array($configuration->getControllerStrategy(), $actionName),
            $configuration,
            $this->request
        );
    }
}
