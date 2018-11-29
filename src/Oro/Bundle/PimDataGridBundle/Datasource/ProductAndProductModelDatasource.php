<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\Datasource;

use Akeneo\Pim\Enrichment\Component\Product\Grid\Query;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\PimDataGridBundle\Extension\Pager\PagerExtension;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Product datasource for the product grid only.
 * It does not handle association grid, published product grid, etc.
 *
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAndProductModelDatasource extends Datasource
{
    /** @var ProductQueryBuilderInterface */
    protected $pqb;

    /** @var ProductQueryBuilderFactoryInterface */
    protected $factory;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var Query\FetchProductAndProductModelRows */
    private $fetchRows;

    /**
     * @param ObjectManager                         $om
     * @param ProductQueryBuilderFactoryInterface   $factory
     * @param NormalizerInterface                   $serializer
     * @param ValidatorInterface                    $validator
     * @param Query\FetchProductAndProductModelRows $fetchRows
     */
    public function __construct(
        ObjectManager $om,
        ProductQueryBuilderFactoryInterface $factory,
        NormalizerInterface $serializer,
        ValidatorInterface $validator,
        Query\FetchProductAndProductModelRows $fetchRows
    ) {
        $this->om = $om;
        $this->factory = $factory;
        $this->normalizer = $serializer;
        $this->fetchRows = $fetchRows;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        $attributesToDisplay = $this->getAttributeCodesToDisplay();

        $channelCode = $this->getConfiguration('scope_code');
        $localeCode = $this->getConfiguration('locale_code');

        $getRowsQueryParameters = new Query\FetchProductAndProductModelRowsParameters(
            $this->pqb,
            $attributesToDisplay,
            $channelCode,
            $localeCode
        );

        $errors = $this->validator->validate($getRowsQueryParameters);
        if (count($errors)) {
            throw new \LogicException(
                sprintf(
                    'Invalid query parameters sent to fetch data in the product and product model datagrid: "%s".',
                    (string) $errors
                )
            );
        }

        $rows = ($this->fetchRows)($getRowsQueryParameters);

        $context = [
            'locales'             => [$localeCode],
            'channels'            => [$channelCode],
            'data_locale'         => $this->getParameters()['dataLocale']
        ];

        $normalizedRows = [
            'data' => [],
            'totalRecords' => $rows->totalCount()
        ];

        foreach ($rows->rows() as $row) {
            $normalizedItem = $this->normalizer->normalize($row, 'datagrid', $context);
            $normalizedRows['data'][] = new ResultRecord($normalizedItem);
        }

        return $normalizedRows;
    }

    /**
     * @return ProductQueryBuilderInterface
     */
    public function getProductQueryBuilder()
    {
        return $this->pqb;
    }

    /**
     * @param string $method the query builder creation method
     * @param array  $config the query builder creation config
     *
     * @return Datasource
     */
    protected function initializeQueryBuilder($method, array $config = [])
    {
        $factoryConfig['repository_parameters'] = $config;
        $factoryConfig['repository_method'] = $method;
        $factoryConfig['default_locale'] = $this->getConfiguration('locale_code');
        $factoryConfig['default_scope'] = $this->getConfiguration('scope_code');
        $factoryConfig['limit'] = (int) $this->getConfiguration(PagerExtension::PER_PAGE_PARAM);
        $factoryConfig['from'] = (int) $this->getConfiguration('from', false) ?? 0;

        $this->pqb = $this->factory->create($factoryConfig);
        $this->qb = $this->pqb->getQueryBuilder();

        return $this;
    }

    /**
     * @return array array of attribute codes
     */
    private function getAttributeCodesToDisplay(): array
    {
        $attributeIdsToDisplay = $this->getConfiguration('displayed_attribute_ids');
        $attributes = $this->getConfiguration('attributes_configuration');

        $attributeCodes = [];
        foreach ($attributes as $attribute) {
            if (in_array($attribute['id'], $attributeIdsToDisplay)) {
                $attributeCodes[] = $attribute['code'];
            }
        }

        return $attributeCodes;
    }
}
