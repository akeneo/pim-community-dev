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

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\GetRecordInformationQueryInterface;
use Akeneo\Pim\Permission\Bundle\User\UserContext;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Oro\Bundle\PimDataGridBundle\Datagrid\Request\RequestParametersExtractorInterface;
use Oro\Bundle\PimDataGridBundle\Extension\Formatter\Property\ProductValue\TwigProperty;

/**
 * Datagrid column formatter for a reference entity or a reference entity collection
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class ReferenceEntityCollectionProperty extends TwigProperty
{
    /** @var RequestParametersExtractorInterface */
    protected $paramsExtractor;

    /** @var UserContext */
    protected $userContext;

    /** @var GetRecordInformationQueryInterface */
    private $getRecordInformationQuery;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    /**
     * @param \Twig_Environment                   $environment
     * @param RequestParametersExtractorInterface $paramsExtractor
     * @param UserContext                         $userContext
     * @param GetRecordInformationQueryInterface  $getRecordInformationQuery
     */
    public function __construct(
        \Twig_Environment $environment,
        RequestParametersExtractorInterface $paramsExtractor,
        UserContext $userContext,
        GetRecordInformationQueryInterface $getRecordInformationQuery = null,
        IdentifiableObjectRepositoryInterface $attributeRepository = null
    ) {
        parent::__construct($environment);

        $this->paramsExtractor = $paramsExtractor;
        $this->userContext = $userContext;
        $this->environment = $environment;
        $this->getRecordInformationQuery = $getRecordInformationQuery;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function format($value)
    {
        if ($this->valueIsEmpty($value)) {
            return null;
        }

        if ($this->isMultipleLinks($value)) {
           return $this->formatMultipleLinks($value);
        }

        return $this->formatSimpleLink($value);
    }

    protected function valueIsEmpty(array $value): bool
    {
        return !isset($value['data']) || empty($value['data']);
    }

    protected function isMultipleLinks(array $data): bool
    {
        return is_array($data['data']);
    }

    protected function formatMultipleLinks($value): string
    {
        return implode(', ', $value['data']);
    }

    protected function formatSimpleLink($value)
    {
        return $value['data'];
    }
}
