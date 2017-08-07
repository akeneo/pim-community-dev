<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Proposal;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Proposal grid context configurator
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ContextConfigurator implements ConfiguratorInterface
{
    /** @var DatagridConfiguration */
    protected $configuration;

    /** @var RequestParameters */
    protected $requestParams;

    /** @var UserContext */
    protected $userContext;

    /** @var Request */
    protected $request;

    /** @var RequestStack */
    protected $requestStack;

    /**
     * @param RequestParameters $requestParams
     * @param UserContext       $userContext
     * @param RequestStack      $requestStack
     */
    public function __construct(
        RequestParameters $requestParams,
        UserContext $userContext,
        RequestStack $requestStack
    ) {
        $this->requestParams = $requestParams;
        $this->userContext = $userContext;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(DatagridConfiguration $configuration)
    {
        $this->configuration = $configuration;

        $path = sprintf(self::SOURCE_PATH, self::REPOSITORY_PARAMETERS_KEY);
        $repositoryParams = $this->configuration->offsetGetByPath($path, null);

        if ($repositoryParams) {
            $params = [];
            foreach ($repositoryParams as $paramName) {
                if ('currentUser' === $paramName) {
                    $params[$paramName] = $this->userContext->getUser();
                } else {
                    $params[$paramName] = $this->requestParams->get($paramName, $this->getRequest()->get($paramName, null));
                }
            }
            $this->configuration->offsetSetByPath($path, $params);
        }
    }

    /**
     * @return Request|null
     */
    protected function getRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }
}
