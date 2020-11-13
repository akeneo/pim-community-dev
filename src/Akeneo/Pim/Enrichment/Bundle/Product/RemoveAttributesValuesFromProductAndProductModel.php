<?php

namespace Akeneo\Pim\Enrichment\Bundle\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeRepository;
use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RemoveAttributesValuesFromProductAndProductModel
{
    private const BATCH_SIZE = 100;

    /** @var ValidatorInterface */
    private $validator;

    /** @var AttributeRepository */
    private $attributeRepository;

    /** @var Client */
    private $elasticsearchClient;
    private $productModelRepository;
    private $productModelSaver;
    private $productRepository;
    private $productSaver;
    private $clearer;

    public function __construct(
        ValidatorInterface $validator,
        AttributeRepository $attributeRepository,
        Client $elasticsearchClient,
        ProductModelRepositoryInterface $productModelRepository,
        BulkSaverInterface $productModelSaver,
        ProductRepositoryInterface $productRepository,
        BulkSaverInterface $productSaver,
        UnitOfWorkAndRepositoriesClearer $clearer
    ) {
        $this->validator = $validator;
        $this->attributeRepository = $attributeRepository;
        $this->elasticsearchClient = $elasticsearchClient;
        $this->productModelRepository = $productModelRepository;
        $this->productModelSaver = $productModelSaver;
        $this->productRepository = $productRepository;
        $this->productSaver = $productSaver;
        $this->clearer = $clearer;
    }

    public function countAffectedProductAndProductModel(array $attributesCodes): int
    {
        $this->validateAttributesCodes($attributesCodes);

        $body = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'terms' => [
                                        'document_type' => [
                                            ProductInterface::class,
                                            ProductModelInterface::class,
                                        ],
                                    ],
                                ],
                            ],
                            'should' => array_map(function (string $attributeCode) {
                                return [
                                    'exists' => ['field' => sprintf('values.%s-*', $attributeCode)],
                                ];
                            }, $attributesCodes),
                            'minimum_should_match' => 1,
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->elasticsearchClient->count($body);

        return (int)$result['count'];
    }

    public function removeAttributesValues(array $attributesCodes, ?callable $progress = null): void
    {
        $this->validateAttributesCodes($attributesCodes);

        $this->removeAttributesValuesFromProductModels($attributesCodes, $progress);
        $this->removeAttributesValuesFromProducts($attributesCodes, $progress);
    }

    private function removeAttributesValuesFromProductModels(array $attributesCodes, ?callable $progress): void
    {
        foreach ($this->fetchAffectedIdentifiersOfType($attributesCodes, ProductModelInterface::class) as $productModelIdentifiers) {
            $productModels = $this->productModelRepository->findBy(['code' => $productModelIdentifiers]);
            $this->productModelSaver->saveAll($productModels);

            if (null !== $progress) {
                $progress(count($productModelIdentifiers));
            }

            $this->clearer->clear();
        }
    }

    private function removeAttributesValuesFromProducts(array $attributesCodes, ?callable $progress): void
    {
        foreach ($this->fetchAffectedIdentifiersOfType($attributesCodes, ProductInterface::class) as $productIdentifiers) {
            $productModels = $this->productRepository->findBy(['identifier' => $productIdentifiers]);
            $this->productSaver->saveAll($productModels);

            if (null !== $progress) {
                $progress(count($productIdentifiers));
            }

            $this->clearer->clear();
        }
    }

    private function validateAttributesCodes(array $attributesCodes)
    {
        if (empty($attributesCodes)) {
            throw new \LogicException('The given attributes codes should not be empty.');
        }

        foreach ($attributesCodes as $attributeCode) {
            $this->validateAttributeCode($attributeCode);
        }
    }

    private function validateAttributeCode(string $attributeCode): void
    {
        $violations = $this->validator->validate($attributeCode, [
            new Assert\NotBlank(),
            new Assert\Length([
                'max' => 100,
            ]),
            new Assert\Regex('/^[a-zA-Z0-9_]+$/'),
            new Assert\Regex('/^(?!(id|iD|Id|ID|associationTypes|categories|categoryId|completeness|enabled|(?i)\bfamily\b|groups|associations|products|scope|treeId|values|category|parent|label|(.)*_(products|groups)|entity_type|attributes)$)/'),
            new Assert\Regex('/^[^\n]+$/D'),
        ]);

        if (count($violations) > 0) {
            throw new \InvalidArgumentException(sprintf('The attribute code "%s" is not valid.', $attributeCode));
        }

        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

        if (null !== $attribute) {
            throw new \InvalidArgumentException(sprintf('The attribute with the code "%s" still exists.',
                $attributeCode));
        }
    }

    private function fetchAffectedIdentifiersOfType(array $attributesCodes, string $class): \Generator
    {
        $body = [
            'size' => self::BATCH_SIZE,
            '_source' => [
                'identifier',
            ],
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'term' => [
                                        'document_type' => $class,
                                    ],
                                ],
                            ],
                            'should' => array_map(function (string $attributeCode) {
                                return [
                                    'exists' => ['field' => sprintf('values.%s-*', $attributeCode)],
                                ];
                            }, $attributesCodes),
                            'minimum_should_match' => 1,
                        ],
                    ],
                ],
            ],
            'sort' => [
                'identifier' => 'asc',
            ],
        ];

        $rows = $this->elasticsearchClient->search($body);

        while (!empty($rows['hits']['hits'])) {
            $identifiers = array_map(function (array $product) {
                return $product['_source']['identifier'];
            }, $rows['hits']['hits']);
            yield $identifiers;
            $body['search_after'] = [end($identifiers)];
            $rows = $this->elasticsearchClient->search($body);
        }
    }
}
