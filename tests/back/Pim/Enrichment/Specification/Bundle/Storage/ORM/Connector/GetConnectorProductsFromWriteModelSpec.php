<?php
/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Storage\ORM\Connector;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Bundle\Storage\ORM\Connector\GetConnectorProductsFromWriteModel;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetMetadataInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeRepository;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GetConnectorProductsFromWriteModelSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $productRepository,
        AttributeRepository $attributeRepository,
        GetMetadataInterface $getMetadata
    ) {

        $attributeRepository->getIdentifierCode()->willReturn('sku');

        $this->beConstructedWith(
            $productRepository,
            $attributeRepository,
            $getMetadata
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GetConnectorProductsFromWriteModel::class);
    }

    function it_provides_connector_products(
        ValueCollectionInterface $valueCollectionProductA,
        ProductInterface $productA,
        FamilyInterface $family,
        IdentifiableObjectRepositoryInterface $productRepository,
        GetMetadataInterface $getMetadata,
        ProductQueryBuilderInterface $pqb
    ) {
        $pqb->execute()->willReturn(new class implements CursorInterface {
            private $identifierResults;

            public function __construct()
            {
                $identifierResultsArrayCollection = new ArrayCollection([
                    new IdentifierResult('jambon', ProductInterface::class),
                ]);
                $this->identifierResults = $identifierResultsArrayCollection->getIterator();
            }
            public function current() { return $this->identifierResults->current(); }
            public function next() { $this->identifierResults->next(); }
            public function key() { return $this->identifierResults->key(); }
            public function count() { return 1; }
            public function valid() { return $this->identifierResults->valid(); }
            public function rewind() { $this->identifierResults->rewind(); }
        });

        $productRepository->findOneByIdentifier('jambon')->willReturn($productA);
        $attributesToFilterOn = [];
        $channelToFilterOn = null;
        $localesToFilterOn = [];

        $date = new \DateTime();
        $immutableDate = DateTimeImmutable::createFromMutable($date);

        $productA->getId()->willReturn(12345);
        $productA->getIdentifier()->willReturn('jambon');
        $productA->getCreated()->willReturn($date);
        $productA->getUpdated()->willReturn($date);
        $productA->isEnabled()->willReturn(true);
        $family->getCode()->willReturn('charcuterie');
        $productA->getFamily()->willReturn($family);
        $productA->getCategoryCodes()->willReturn([]);
        $productA->getGroupCodes()->willReturn([]);
        $productA->isVariant()->willReturn(false);
        $productA->getAllAssociations()->willReturn(new ArrayCollection());
        $productA->getValues()->willReturn($valueCollectionProductA);
        $valueCollectionProductA->filter(Argument::type(\Closure::class))->willReturn($valueCollectionProductA);

        $valueCollectionProductA->removeByAttributeCode('sku')->shouldBeCalled();

        $getMetadata->forProduct($productA)->willReturn(['workflow_status' => 'working_copy']);

        $this->fromProductQueryBuilder($pqb, $attributesToFilterOn, $channelToFilterOn, $localesToFilterOn)->shouldBeLike(
            new ConnectorProductList(1, [
                new ConnectorProduct(
                    12345,
                    'jambon',
                    $immutableDate,
                    $immutableDate,
                    true,
                    'charcuterie',
                    [],
                    [],
                    null,
                    [],
                    ['workflow_status' => 'working_copy'],
                    $valueCollectionProductA->getWrappedObject()
                )
            ])
        );

    }
}
