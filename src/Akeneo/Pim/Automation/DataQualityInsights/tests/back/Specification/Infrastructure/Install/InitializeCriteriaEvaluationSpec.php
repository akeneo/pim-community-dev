<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Install;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Install\InitializeCriteriaEvaluation;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\FetchMode;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

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
        $featureFlag,
        $db,
        $createProductsCriteriaEvaluations,
        ResultStatement $resultStatement
    ) {
        $featureFlag->isEnabled()->willReturn(true);

        $db->executeQuery('select count(*) as nb from pim_catalog_product where product_model_id is null')->willReturn($resultStatement);
        $resultStatement->fetch()->willReturn(['nb' => 0]);

        $createProductsCriteriaEvaluations->createAll(Argument::any())->shouldNotBeCalled();

        $this->initialize();
    }

    public function it_initialize_products_evaluation(
        $featureFlag,
        $db,
        $createProductsCriteriaEvaluations,
        ResultStatement $countResultStatement,
        ResultStatement $productIdsResultStatement
    ) {
        $featureFlag->isEnabled()->willReturn(true);

        $db->executeQuery('select count(*) as nb from pim_catalog_product where product_model_id is null')->willReturn($countResultStatement);
        $countResultStatement->fetch()->willReturn(['nb' => 99]);

        $db->query(Argument::any())->willReturn($productIdsResultStatement);

        $ids = range(1, 100);
        $productIdsResultStatement->fetchAll(FetchMode::COLUMN, 0)->willReturn($ids);

        $createProductsCriteriaEvaluations->createAll(Argument::type('array'))->shouldBeCalled();

        $this->initialize();
    }
}
