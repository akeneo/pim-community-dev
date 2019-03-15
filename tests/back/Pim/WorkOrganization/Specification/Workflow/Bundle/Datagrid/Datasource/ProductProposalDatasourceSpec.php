<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Datasource;

use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Datagrid;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ParameterizableInterface;
use Oro\Bundle\PimDataGridBundle\Extension\Pager\PagerExtension;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Datasource\ProductProposalDatasource;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductProposalDatasourceSpec extends ObjectBehavior
{
    public function let(
        ProductQueryBuilderFactoryInterface $factory,
        NormalizerInterface $productProposalNormalizer
    ) {
        $this->beConstructedWith($factory, $productProposalNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductProposalDatasource::class);
    }

    function it_is_a_datasource()
    {
        $this->shouldImplement(DatasourceInterface::class);
        $this->shouldImplement(ParameterizableInterface::class);
    }

    function it_gets_product_proposals(
        $factory,
        $productProposalNormalizer,
        Datagrid $datagrid,
        ProductQueryBuilderInterface $pqb,
        EntityWithValuesDraftInterface $productProposal,
        CursorInterface $productProposalCursor
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
            PagerExtension::PER_PAGE_PARAM => 15
        ];

        $factory->create([
            'repository_parameters' => [],
            'repository_method'     => 'createQueryBuilder',
            'limit'                 => 15,
            'from'                  => 0,
        ])->willReturn($pqb);

        $pqb->getQueryBuilder()->shouldBeCalledTimes(1);
        $pqb->execute()->willReturn($productProposalCursor);
        $productProposalCursor->count()->willReturn(1);

        $productProposal->hasChanges()->willReturn(true);
        $productProposal->getId()->willReturn(1);

        $productProposalCursor->rewind()->shouldBeCalled();
        $productProposalCursor->valid()->willReturn(true, false);
        $productProposalCursor->current()->willReturn($productProposal);
        $productProposalCursor->next()->shouldBeCalled();

        $this->process($datagrid, $config);

        $productProposalNormalizer->normalize(
            $productProposal,
            'datagrid'
        )->willReturn(
            [
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
            ]
        );

        $results = $this->getResults();
        $results->shouldBeArray();
        $results->shouldHaveCount(2);
        $results->shouldHaveKey('data');
        $results->shouldHaveKeyWithValue('totalRecords', 1);
        $results['data']->shouldBeArray();
        $results['data']->shouldHaveCount(1);
        $results['data']->shouldBeAnArrayOfInstanceOf(ResultRecord::class);
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
