<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Category\back\tests\Integration\Application\Converter;

use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class StandardFormatToUserIntentsIntegration extends TestCase
{
    /** @test */
    public function it_convert_label_update_standard_format_to_setlabel_user_intents(): void
    {
        $standardFormat = [
            "id" => 1,
            "labels" => [
                "en_US" => "sausages",
                "fr_FR" => "saucisses"
            ]
        ];
        $converter = $this->get('Akeneo\Category\Application\Converter\StandardFormatToUserIntentsInterface');
        $result = $converter->convert($standardFormat);

        Assert::assertEqualsCanonicalizing(
            [
                new SetLabel("en_US", "sausages"),
                new SetLabel("fr_FR", "saucisses"),
            ],
            $result
        );
    }

    /** @test */
    public function it_throws_an_exception_when_field_has_no_associated_factory(): void
    {
        $nonExistentAttributeCode = 'foobar';

        $standardFormat = [
            "id" => 1,
            $nonExistentAttributeCode => "foo",
            "labels" => [
                "en_US" => "socks",
                "fr_FR" => "chaussettes"
            ]
        ];
        $converter = $this->get('Akeneo\Category\Application\Converter\StandardFormatToUserIntentsInterface');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot create userIntent from ".$nonExistentAttributeCode." fieldName");

        $converter->convert($standardFormat);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
