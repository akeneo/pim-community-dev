<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Install;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Install\InitializeCriteriaEvaluation;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

class InitializeCriteriaEvaluationSpec extends ObjectBehavior
{
    public function let(
        FeatureFlag $featureFlag,
        Connection $db,
        CreateCriteriaEvaluations $createProductsCriteriaEvaluations
    ) {
        $this->beConstructedWith($featureFlag, $db, $createProductsCriteriaEvaluations);
    }

    public function it_is_initializable()
    {
        $this->beAnInstanceOf(InitializeCriteriaEvaluation::class);
    }

    public function it_throws_an_exception_if_feature_is_disabled($featureFlag)
    {
        $featureFlag->isEnabled()->willReturn(false);

        $this->shouldThrow(\RuntimeException::class)->during('initialize');
    }

    public function it_initialize_nothing_if_their_is_no_product(
        FeatureFlag $featureFlag,
        Connection $db,
        $createProductsCriteriaEvaluations,
        Result $resultStatement
    ) {
        $featureFlag->isEnabled()->willReturn(true);

        $db->executeQuery('select count(*) as nb from pim_catalog_product where product_model_id is null')
            ->willReturn($resultStatement);
        $resultStatement->fetchAssociative()->willReturn(['nb' => 0]);

        $createProductsCriteriaEvaluations->createAll(Argument::any())->shouldNotBeCalled();

        $this->initialize();
    }

    public function it_initialize_products_evaluation(
        FeatureFlag $featureFlag,
        Connection $db,
        CreateCriteriaEvaluations $createProductsCriteriaEvaluations,
        Result $countResult,
        Result $productIdsResult
    ) {
        $featureFlag->isEnabled()->willReturn(true);

        $db->executeQuery('select count(*) as nb from pim_catalog_product where product_model_id is null')
            ->willReturn($countResult);
        $countResult->fetchAssociative()->willReturn(['nb' => 99]);

        $db->executeQuery(Argument::any())->willReturn($productIdsResult);

        $ids = [];
        for ($i = 0; $i < 100; $i++) {
            $ids[] = Uuid::uuid4()->toString();
        }
        $productIdsResult->fetchFirstColumn()->willReturn($ids);

        $createProductsCriteriaEvaluations->createAll(Argument::type(ProductUuidCollection::class))->shouldBeCalled();

        $this->initialize();
    }
}
