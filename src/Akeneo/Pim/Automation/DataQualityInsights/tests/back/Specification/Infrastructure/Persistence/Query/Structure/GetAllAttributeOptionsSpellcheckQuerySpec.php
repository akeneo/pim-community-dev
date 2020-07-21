<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure;


use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;

class GetAllAttributeOptionsSpellcheckQuerySpec extends ObjectBehavior
{
    public function let(Connection $dbConnection)
    {
        $this->beConstructedWith($dbConnection);
    }

    public function it_formats_fetched_row()
    {
        $attribute_code = 'my_code';
        $attribute_option_code = 'my_option';
        $evaluated_at = '2020-06-10 10:00:00';
        $result = '{"de_DE": true, "en_US": true, "fr_FR": true}';

        $expectedEvaluationResult = (new SpellcheckResultByLocaleCollection())
            ->add(new LocaleCode('de_DE'), new SpellCheckResult(true))
            ->add(new LocaleCode('en_US'), new SpellCheckResult(true))
            ->add(new LocaleCode('fr_FR'), new SpellCheckResult(true))
        ;

        $expected = new AttributeOptionSpellcheck(
            new AttributeOptionCode(new AttributeCode($attribute_code), $attribute_option_code),
            new \DateTimeImmutable($evaluated_at),
            $expectedEvaluationResult
        );

        $this->format($attribute_code, $attribute_option_code, $evaluated_at, $result)->shouldBeLike($expected);
    }

    public function it_formats_fetched_row_when_result_is_invalid()
    {
        $attribute_code = 'my_code';
        $attribute_option_code = 'my_option';
        $evaluated_at = '2020-06-10 10:00:00';
        $result = 'invalid_json_format';

        $expectedEvaluationResult = new SpellcheckResultByLocaleCollection();

        $expected = new AttributeOptionSpellcheck(
            new AttributeOptionCode(new AttributeCode($attribute_code), $attribute_option_code),
            new \DateTimeImmutable($evaluated_at),
            $expectedEvaluationResult
        );

        $this->format($attribute_code, $attribute_option_code, $evaluated_at, $result)->shouldBeLike($expected);
    }
}
