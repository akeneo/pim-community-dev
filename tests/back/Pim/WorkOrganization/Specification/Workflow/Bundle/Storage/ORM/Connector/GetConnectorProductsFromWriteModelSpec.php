<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\ORM\Connector;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ExternalApi\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\ExternalApi\AttributeRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\ORM\Connector\GetConnectorProductsFromWriteModel;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\ORM\Connector\GetWorkflowStatusForProduct;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class GetConnectorProductsFromWriteModelSpec extends ObjectBehavior
{
    function let(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        EntityWithValuesDraftRepositoryInterface $draftRepository,
        AttributeRepositoryInterface $attributeRepository,
        ProductRepositoryInterface $productRepository,
        TokenInterface $token
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUsername()->willReturn('mary');

        $authorizationChecker->isGranted(Argument::cetera())->willReturn(true);

        $attributeRepository->getIdentifierCode()->willReturn('sku');

        $this->beConstructedWith(
            new GetWorkflowStatusForProduct(
                $authorizationChecker->getWrappedObject(),
                $tokenStorage->getWrappedObject(),
                $draftRepository->getWrappedObject()
            ),
            $attributeRepository,
            $productRepository
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
        ProductRepositoryInterface $productRepository
    ) {
        $productRepository->findOneByIdentifier('product_1')->willReturn($productA);
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
        $productA->setValues($valueCollectionProductA)->shouldBeCalled();

        $this->fromProductIdentifiers(['product_1'], $attributesToFilterOn, $channelToFilterOn, $localesToFilterOn)->shouldBeLike([
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
        ]);

    }
}
