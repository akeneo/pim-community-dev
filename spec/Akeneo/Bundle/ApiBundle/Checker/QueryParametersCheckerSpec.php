<?php

namespace spec\Akeneo\Bundle\RuleEngineBundle\Denormalizer;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\ApiBundle\Checker\QueryParametersCheckerInterface;
use PimEnterprise\Bundle\ApiBundle\Checker\QueryParametersChecker;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class QueryParametersCheckerSpec extends ObjectBehavior
{
    function let(
        QueryParametersCheckerInterface $queryParametersChecker,
        AuthorizationCheckerInterface $authorizationChecker,
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $categoryRepository
    ) {
        $this->beConstructedWith(
            $queryParametersChecker,
            $authorizationChecker,
            $localeRepository,
            $attributeRepository,
            $categoryRepository
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(QueryParametersChecker::class);
    }

    function it_should_be_a_query_param_checker()
    {
        $this->shouldHaveType(QueryParametersCheckerInterface::class);
    }

    function it_checks_locale_parameter()
    {
        $this->checkLocalesParameters();
    }
}
