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

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Bundle\Datagrid\Extension\Formatter\Property\ProductValue;

use Akeneo\Pim\Permission\Bundle\User\UserContext;
use Oro\Bundle\PimDataGridBundle\Datagrid\Request\RequestParametersExtractorInterface;
use Oro\Bundle\PimDataGridBundle\Extension\Formatter\Property\ProductValue\TwigProperty;
use Twig\Environment;

/**
 * Datagrid column formatter for a reference entity or a reference entity collection
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class ReferenceEntityCollectionProperty extends TwigProperty
{
    protected RequestParametersExtractorInterface $paramsExtractor;
    protected UserContext $userContext;

    public function __construct(
        Environment $environment,
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
            $data = $value['data'];
            return is_array($data) ? implode(', ', $data) : $data;
        }

        return null;
    }
}
