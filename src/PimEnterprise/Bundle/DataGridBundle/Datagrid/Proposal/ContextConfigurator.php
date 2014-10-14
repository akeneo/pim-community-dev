<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\Datagrid\Proposal;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ConfiguratorInterface;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * Proposal grid context configurator
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ContextConfigurator implements ConfiguratorInterface
{
    /** @staticvar string */
    const REPOSITORY_PARAMETERS_KEY = 'repository_parameters';

    /** @staticvar string */
    const SOURCE_PATH = '[source][%s]';

    /** @var DatagridConfiguration */
    protected $configuration;

    /** @var RequestParameters */
    protected $requestParams;

    /** @var UserContext */
    protected $userContext;

    /** @var Request */
    protected $request;

    /**
     * @param RequestParameters $requestParams
     * @param UserContext       $userContext
     */
    public function __construct(
        RequestParameters $requestParams,
        UserContext $userContext
    ) {
        $this->requestParams = $requestParams;
        $this->userContext   = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(DatagridConfiguration $configuration)
    {
        $this->configuration = $configuration;

        $path             = sprintf(self::SOURCE_PATH, self::REPOSITORY_PARAMETERS_KEY);
        $repositoryParams = $this->configuration->offsetGetByPath($path, null);

        if ($repositoryParams) {
            $params = [];
            foreach ($repositoryParams as $paramName) {
                if ('currentUser' === $paramName) {
                    $params[$paramName] = $this->userContext->getUser();
                } else {
                    $params[$paramName] = $this->requestParams->get($paramName, $this->request->get($paramName, null));
                }
            }
            $this->configuration->offsetSetByPath($path, $params);
        }
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }
}
