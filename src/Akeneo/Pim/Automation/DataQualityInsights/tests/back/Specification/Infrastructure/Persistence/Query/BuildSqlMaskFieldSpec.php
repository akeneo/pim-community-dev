<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Completeness\AttributeCase;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class BuildSqlMaskFieldSpec extends ObjectBehavior
{
    public function it_returns_masks_when_no_attribute_case()
    {
        $this->beConstructedWith([]);
        $sql = <<<SQL
JSON_ARRAYAGG(
    CONCAT(
        attribute.code,
        '-',
        IF(attribute.is_scopable, channel_locale.channel_code, '<all_channels>'),
        '-',
        IF(attribute.is_localizable, channel_locale.locale_code, '<all_locales>')
    )
)
AS mask
SQL;

        $this->__invoke()->shouldReturn($sql);
    }

    public function it_returns_masks_whith_attribute_cases(AttributeCase $attributeTypeA, AttributeCase $attributeTypeB)
    {
        $this->beConstructedWith([
            $attributeTypeA,
            $attributeTypeB
        ]);
        $attributeTypeA->getCase()->willReturn(
        "WHEN attribute.attribute_type = 'typeA'
            THEN 'TypeA'"
        );
        $attributeTypeB->getCase()->willReturn(
        "WHEN attribute.attribute_type = 'typeB'
            THEN 'TypeB'"
        );

        $sql = <<<SQL
JSON_ARRAYAGG(
    CONCAT(
        CASE
            WHEN attribute.attribute_type = 'typeA'
                THEN 'TypeA'
            WHEN attribute.attribute_type = 'typeB'
                THEN 'TypeB'
            ELSE attribute.code
        END,
        '-',
        IF(attribute.is_scopable, channel_locale.channel_code, '<all_channels>'),
        '-',
        IF(attribute.is_localizable, channel_locale.locale_code, '<all_locales>')
    )
) AS mask
SQL;
        $this->__invoke()->shouldHaveSqlQueryEqualsTo($sql);
    }

    public function getMatchers(): array
    {
        return [
            'haveSqlQueryEqualsTo' => function ($subject, $expected) {
                // Remove all spaces to compare sql query
                $strippedSubject = trim(preg_replace('/\s+/', ' ', $subject));
                $strippedExpected = trim(preg_replace('/\s+/', ' ', $expected));
                if ($strippedSubject !== $strippedExpected) {
                    throw new FailureException(sprintf(
                        'The mask has not the expected sql structure  :' . PHP_EOL . '%s ' . PHP_EOL . 'but we got this : ' . PHP_EOL . '%s',
                        $strippedExpected,
                        $strippedSubject
                    ));
                }
                return true;
            }
        ];
    }
}
