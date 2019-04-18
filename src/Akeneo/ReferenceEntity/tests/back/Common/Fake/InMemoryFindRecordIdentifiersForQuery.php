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

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindIdentifiersForQueryInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\IdentifiersForQueryResult;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author    Julien Sanchez <julienakeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindRecordIdentifiersForQuery implements FindIdentifiersForQueryInterface
{
    private const API_REST_PREFIX_URL = '/api/rest/';

    /** @var string[] */
    private $requestContracts = [
        'src/Akeneo/ReferenceEntity/tests/shared/responses/Record/Search/city_and_color_filtered.json',
        'src/Akeneo/ReferenceEntity/tests/shared/responses/Record/Search/code_filtered.json',
        'src/Akeneo/ReferenceEntity/tests/shared/responses/Record/Search/code_filtered_with_not_in_operator.json',
        'src/Akeneo/ReferenceEntity/tests/shared/responses/Record/Search/color_filtered.json',
        'src/Akeneo/ReferenceEntity/tests/shared/responses/Record/Search/desynchronized_reference_entity_identifier.json',
        'src/Akeneo/ReferenceEntity/tests/shared/responses/Record/Search/no_result.json',
        'src/Akeneo/ReferenceEntity/tests/shared/responses/Record/Search/not_filtered.json',
        'src/Akeneo/ReferenceEntity/tests/shared/responses/Record/Search/uncomplete_filtered.json',
        'src/Akeneo/ReferenceEntity/tests/shared/responses/Record/Search/city_filtered.json',
        'src/Akeneo/ReferenceEntity/tests/shared/responses/Record/Search/code_label_and_code_filtered.json',
        'src/Akeneo/ReferenceEntity/tests/shared/responses/Record/Search/complete_filtered.json',
        'src/Akeneo/ReferenceEntity/tests/shared/responses/Record/Search/no_result_fr.json',
        'src/Akeneo/ReferenceEntity/tests/shared/responses/Record/Search/ok.json',
    ];

    /** @var string[] */
    private $requestContractsApi = [
        'src/Akeneo/ReferenceEntity/tests/shared/responses/Record/Connector/Distribute/successful_brand_records_for_ecommerce_channel.json',
        'src/Akeneo/ReferenceEntity/tests/shared/responses/Record/Connector/Distribute/successful_complete_brand_records.json',
        'src/Akeneo/ReferenceEntity/tests/shared/responses/Record/Connector/Distribute/successful_brand_records_page_1.json',
        'src/Akeneo/ReferenceEntity/tests/shared/responses/Record/Connector/Distribute/successful_brand_records_page_2.json',
        'src/Akeneo/ReferenceEntity/tests/shared/responses/Record/Connector/Distribute/successful_brand_records_page_3.json',
        'src/Akeneo/ReferenceEntity/tests/shared/responses/Record/Connector/Distribute/successful_brand_records_page_4.json',
        'src/Akeneo/ReferenceEntity/tests/shared/responses/Record/Connector/Distribute/updated_since_brand_records_page_1.json',
    ];

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }
    /**
     * {@inheritdoc}
     */
    public function __invoke(RecordQuery $query): IdentifiersForQueryResult
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if (null !== $currentRequest && self::API_REST_PREFIX_URL === substr($currentRequest->getPathInfo(), 0, 10)) {
            $queryParameters = $this->getQueryParametersFromRequest($currentRequest);
            $items = $this->getItemsForQueryParameters($queryParameters);
            $identifiers = array_map(function ($item) {
                return RecordIdentifier::fromString(sprintf('%s_fingerprint', $item['code']))->normalize();
            }, $items);
        } else {
            $items = $this->getItemsForFilters($query->getFilters());
            $identifiers = array_map(function ($item) {
                return $item['identifier'];
            }, $items);
        }

        return new IdentifiersForQueryResult($identifiers, count($identifiers));
    }

    private function getItemsForFilters(array $filters): array
    {
        foreach ($this->requestContracts as $requestContract) {
            $requestContractData = json_decode(file_get_contents($requestContract), true);

            if ($requestContractData['request']['body']['filters'] === $filters) {
                return $requestContractData['response']['body']['items'];
            }
        }

        throw new InvalidArgumentException(sprintf('Cannot find items for filters %s', json_encode($filters)));
    }

    private function getItemsForQueryParameters(array $queryParameters): array
    {
        foreach ($this->requestContractsApi as $requestContract) {
            $requestContractData = json_decode(file_get_contents($requestContract), true);

            if ($requestContractData['request']['query'] === $queryParameters) {
                return $requestContractData['response']['body']['_embedded']['items'];
            }
        }

        throw new InvalidArgumentException(sprintf('Cannot find items for query parameters %s', json_encode($queryParameters)));
    }

    private function getQueryParametersFromRequest(Request $request): array
    {
        $queryParameters = [];
        $queryParameters['referenceEntityIdentifier'] = $request->get('referenceEntityIdentifier');
        $search = $request->get('search', null);
        if (null !== $search) {
            $queryParameters['search'] = $search;
        }
        $searchAfter = $request->get('search_after', null);
        if (null !== $searchAfter) {
            $queryParameters['search_after'] = $searchAfter;
        }
        $channel = $request->get('channel', null);
        if (null !== $channel) {
            $queryParameters['channel'] = $channel;
        }
        $locale = $request->get('locale', null);
        if (null !== $locale) {
            $queryParameters['locale'] = $locale;
        }


        return $queryParameters;
    }
}
