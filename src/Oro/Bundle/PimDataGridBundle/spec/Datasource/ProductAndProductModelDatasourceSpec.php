<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Datasource;

use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Rows;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datagrid\Datagrid;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ParameterizableInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ProductAndProductModelDatasource;
use Oro\Bundle\PimDataGridBundle\Extension\Pager\PagerExtension;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductAndProductModelDatasourceSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        ProductQueryBuilderFactoryInterface $pqbFactory,
        NormalizerInterface $rowNormalizer,
        ValidatorInterface $validator,
        Query\FetchProductAndProductModelRows $query
    ) {
        $this->beConstructedWith($objectManager, $pqbFactory, $rowNormalizer, $validator, $query);

        $this->setParameters(['dataLocale' => 'fr_FR']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductAndProductModelDatasource::class);
    }

    function it_is_a_datasource()
    {
        $this->shouldImplement(DatasourceInterface::class);
        $this->shouldImplement(ParameterizableInterface::class);
    }

    function it_fetch_product_and_product_model_rows(
        $pqbFactory,
        $rowNormalizer,
        $query,
        $validator,
        Datagrid $datagrid,
        ProductQueryBuilderInterface $pqb
    ) {
        $violations = new ConstraintViolationList();
        $validator
            ->validate(Argument::type(Query\FetchProductAndProductModelRowsParameters::class))
            ->willReturn($violations);

        $config = [
            'displayed_attribute_ids' => [1, 2],
            'attributes_configuration' => [
                'attribute_1' => [
                    'id' => 1,
                    'code' => 'attribute_1'
                ],
                'attribute_2' => [
                    'id' => 2,
                    'code' => 'attribute_2'
                ],
                'attribute_3' => [
                    'id' => 3,
                    'code' => 'attribute_3'
                ],
            ],
            'locale_code' => 'fr_FR',
            'scope_code' => 'ecommerce',
            PagerExtension::PER_PAGE_PARAM => 15
        ];

        $pqbFactory->create([
            'repository_parameters' => [],
            'repository_method'     => 'createQueryBuilder',
            'limit'                 => 15,
            'from'                  => 0,
            'default_locale'        => 'fr_FR',
            'default_scope'         => 'ecommerce',
        ])->willReturn($pqb);

        $pqb->getQueryBuilder()->shouldBeCalledTimes(1);

         $row = Row::fromProduct(
            'identifier',
            'family label',
            ['group_1', 'group_2'],
            true,
            new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
            'label',
            null,
            90,
            1,
            'parent_code',
            new WriteValueCollection()
        );
        $query->__invoke(new Query\FetchProductAndProductModelRowsParameters(
            $pqb->getWrappedObject(),
            ['attribute_1', 'attribute_2'],
            'ecommerce',
            'fr_FR'
        ))->willReturn(new Rows([$row], 1));
        $this->process($datagrid, $config);

        $rowNormalizer->normalize($row, 'datagrid', [
            'locales'       => ['fr_FR'],
            'channels'      => ['ecommerce'],
            'data_locale'   => 'fr_FR'
        ])->willReturn([
            'identifier'   => 'identifier',
            'family'       => 'family label',
            'groups'       => 'group_1,group_2',
            'enabled'      => true,
            'values'       => [],
            'created'      => '2018-05-23T15:55:50+01:00',
            'updated'      => '2018-05-23T15:55:50+01:00',
            'label'        => 'data',
            'image'        => null,
            'completeness' => 90,
            'document_type' => 'product',
            'technical_id' => 1,
            'id'           => 1,
            'search_id' => 'product_1',
            'is_checked' => true,
            'complete_variant_product' => [],
            'parent' => 'parent_code',
        ]);

        $results = $this->getResults();
        $results->shouldBeArray();
        $results->shouldHaveCount(2);
        $results->shouldHaveKey('data');
        $results->shouldHaveKeyWithValue('totalRecords', 1);
        $results['data']->shouldBeArray();
        $results['data']->shouldHaveCount(1);
        $results['data']->shouldBeAnArrayOfInstanceOf(ResultRecord::class);
    }

    function it_does_not_fetch_rows_when_query_parameters_are_invalid(
        $validator,
        $pqbFactory,
        ConstraintViolation $constraint,
        Datagrid $datagrid,
        ProductQueryBuilderInterface $pqb
    ) {
        $config = [
            'displayed_attribute_ids' => [1, 2],
            'attributes_configuration' => [
                'attribute_1' => [
                    'id' => 1,
                    'code' => 'attribute_1'
                ],
                'attribute_2' => [
                    'id' => 2,
                    'code' => 'attribute_2'
                ],
                'attribute_3' => [
                    'id' => 3,
                    'code' => 'attribute_3'
                ],
            ],
            'locale_code' => 'fr_FR',
            'scope_code' => 'ecommerce',
            PagerExtension::PER_PAGE_PARAM => 15
        ];
        $pqbFactory->create([
            'repository_parameters' => [],
            'repository_method'     => 'createQueryBuilder',
            'limit'                 => 15,
            'from'                  => 0,
            'default_locale'        => 'fr_FR',
            'default_scope'         => 'ecommerce',
        ])->willReturn($pqb);

        $pqb->getQueryBuilder()->shouldBeCalledTimes(1);
        $this->process($datagrid, $config);

        $violations = new ConstraintViolationList([$constraint->getWrappedObject()]);
        $constraint->__toString()->willReturn('error');
        $validator
            ->validate(Argument::type(Query\FetchProductAndProductModelRowsParameters::class))
            ->willReturn($violations);


        $this->shouldThrow(
            \LogicException::class
        )->during(
            'getResults',
            []
        );
    }

    public function getMatchers(): array
    {
        return [
            'beAnArrayOfInstanceOf' => function (array $subjects, $class) {
                foreach ($subjects as $subject) {
                    if (!$subject instanceof $class) {
                        return false;
                    }
                }

                return true;
            },
        ];
    }
}
