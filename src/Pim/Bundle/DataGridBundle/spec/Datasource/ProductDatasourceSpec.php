<?php

namespace spec\Pim\Bundle\DataGridBundle\Datasource;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datagrid\Datagrid;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\DataGridBundle\Datasource\ParameterizableInterface;
use Pim\Bundle\DataGridBundle\Datasource\ProductDatasource;
use Pim\Bundle\DataGridBundle\EventSubscriber\FilterEntityWithValuesSubscriber;
use Pim\Bundle\DataGridBundle\EventSubscriber\FilterEntityWithValuesSubscriberConfiguration;
use Pim\Bundle\DataGridBundle\Extension\Pager\PagerExtension;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductDatasourceSpec extends ObjectBehavior
{
    public function let(
        ObjectManager $objectManager,
        ProductQueryBuilderFactoryInterface $pqbFactory,
        NormalizerInterface $productNormalizer,
        FilterEntityWithValuesSubscriber $subscriber
    ) {
        $this->beConstructedWith($objectManager, $pqbFactory, $productNormalizer, $subscriber);

        $this->setParameters(['dataLocale' => 'fr_FR']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductDatasource::class);
    }

    function it_is_a_datasource()
    {
        $this->shouldImplement(DatasourceInterface::class);
        $this->shouldImplement(ParameterizableInterface::class);
    }

    function it_gets_products(
        $pqbFactory,
        $productNormalizer,
        $subscriber,
        Datagrid $datagrid,
        ProductQueryBuilderInterface $pqb,
        ProductInterface $product1,
        CursorInterface $productCursor
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

            'association_type_id' => 2,
            'current_group_id' => 3,
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
        $pqb->execute()->willReturn($productCursor);
        $productCursor->count()->willReturn(1);

        $productCursor->rewind()->shouldBeCalled();
        $productCursor->valid()->willReturn(true, false);
        $productCursor->current()->willReturn($product1);
        $productCursor->next()->shouldBeCalled();

        $this->process($datagrid, $config);

        $productNormalizer->normalize($product1, 'datagrid', [
            'locales'       => ['fr_FR'],
            'channels'      => ['ecommerce'],
            'data_locale'   => 'fr_FR',
            'association_type_id' => 2,
            'current_group_id' => 3
        ])->willReturn([
            'identifier'       => 'product_1',
            'family'           => null,
            'enabled'          => true,
            'label'            => 'foo',
            'values'           => [],
            'created'          => '2000-01-01',
            'updated'          => '2000-01-01',
            'compleneteness'   => null,
            'variant_products' => null,
            'document_type'    => null,
        ]);

        $subscriber
            ->configure(FilterEntityWithValuesSubscriberConfiguration::filterEntityValues(['attribute_1', 'attribute_2']))
            ->shouldBeCalled();

        $results = $this->getResults();
        $results->shouldBeArray();
        $results->shouldHaveCount(2);
        $results->shouldHaveKey('data');
        $results->shouldHaveKeyWithValue('totalRecords', 1);
        $results['data']->shouldBeArray();
        $results['data']->shouldHaveCount(1);
        $results['data']->shouldBeAnArrayOfInstanceOf(ResultRecord::class);
    }

    public function getMatchers()
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
