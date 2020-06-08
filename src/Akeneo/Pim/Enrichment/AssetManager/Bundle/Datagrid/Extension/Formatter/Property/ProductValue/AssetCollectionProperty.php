<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\AssetManager\Bundle\Datagrid\Extension\Formatter\Property\ProductValue;

use Akeneo\Pim\Permission\Bundle\User\UserContext;
use Oro\Bundle\PimDataGridBundle\Datagrid\Request\RequestParametersExtractorInterface;
use Oro\Bundle\PimDataGridBundle\Extension\Formatter\Property\ProductValue\TwigProperty;

/**
 * Datagrid column formatter for an asset family or an asset family collection
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class AssetCollectionProperty extends TwigProperty
{
    /** @var RequestParametersExtractorInterface */
    protected $paramsExtractor;

    /** @var UserContext */
    protected $userContext;

    /**
     * @param \Twig_Environment                   $environment
     * @param RequestParametersExtractorInterface $paramsExtractor
     * @param UserContext                         $userContext
     */
    public function __construct(
        \Twig_Environment $environment,
        RequestParametersExtractorInterface $paramsExtractor,
        UserContext $userContext
    ) {
        parent::__construct($environment);

        $this->paramsExtractor = $paramsExtractor;
        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    protected function format($value)
    {
        if (isset($value['data']) && !empty($value['data'])) {
            return $value['data'];
        }

        return null;
    }
}
