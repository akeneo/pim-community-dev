<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Storage\ORM\Connector;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Bundle\Storage\ORM\Connector\GetConnectorProductModels;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModelList;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\GetMetadataInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetConnectorProductModelsSpec extends ObjectBehavior
{
    function let(
        ProductModelRepositoryInterface $productModelRepository,
        GetMetadataInterface $getMetadata
    ) {
        $this->beConstructedWith($productModelRepository, $getMetadata);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GetConnectorProductModels::class);
    }

    function it_provides_connector_product_models(
        ValueCollectionInterface $valueCollection,
        ProductModelInterface $productModel,
        FamilyVariantInterface $variant,
        GetMetadataInterface $getMetadata,
        ProductQueryBuilderInterface $productQueryBuilder,
        ProductModelRepositoryInterface $productModelRepository
    ) {
        $productQueryBuilder->execute()->willReturn(new class implements CursorInterface {
            private $identifierResults;

            public function __construct()
            {
                $identifierResultsArrayCollection = new ArrayCollection([
                    new IdentifierResult('jambon', ProductModelInterface::class),
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
        
        $productModelRepository->findOneByIdentifier('jambon')->willReturn($productModel);
        $attributesToFilterOn = [];
        $channelToFilterOn = null;
        $localesToFilterOn = [];

        $date = new \DateTime();
        $immutableDate = DateTimeImmutable::createFromMutable($date);

        $productModel->getId()->willReturn(12345);
        $productModel->getCode()->willReturn('jambon');
        $productModel->getCreated()->willReturn($date);
        $productModel->getUpdated()->willReturn($date);
        $variant->getCode()->willReturn('charcuterie');
        $productModel->getParent()->willReturn(null);
        $productModel->getFamilyVariant()->willReturn($variant);
        $productModel->getCategoryCodes()->willReturn([]);
        $productModel->getAllAssociations()->willReturn(new ArrayCollection());
        $productModel->getValues()->willReturn($valueCollection);
        $valueCollection->filter(Argument::type(\Closure::class))->willReturn($valueCollection);

        $productModel->setValues($valueCollection)->shouldBeCalled();

        $getMetadata->forProductModel($productModel)->willReturn(['workflow_status' => 'working_copy']);

        $this->fromProductQueryBuilder(
            $productQueryBuilder,
            $attributesToFilterOn,
            $channelToFilterOn,
            $localesToFilterOn
        )->shouldBeLike(
            new ConnectorProductModelList(1, [
                new ConnectorProductModel(
                    12345,
                    'jambon',
                    $immutableDate,
                    $immutableDate,
                    null,
                    'charcuterie',
                    ['workflow_status' => 'working_copy'],
                    [],
                    [],
                    $valueCollection->getWrappedObject()
                )
            ])
        );
    }
}
