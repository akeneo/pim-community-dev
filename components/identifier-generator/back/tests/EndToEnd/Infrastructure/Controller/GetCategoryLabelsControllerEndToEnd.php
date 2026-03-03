<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\Infrastructure\Controller;

use Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\ControllerEndToEndTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCategoryLabelsControllerEndToEnd extends ControllerEndToEndTestCase
{
    /** @test */
    public function it_should_redirect_on_non_xhr_request(): void
    {
        $this->loginAs('Julia');
        $this->callRoute('akeneo_identifier_generator_get_category_labels', [
            'HTTP_X-Requested-With' => 'toto',
        ]);
        $response = $this->client->getResponse();
        Assert::AssertSame(Response::HTTP_FOUND, $response->getStatusCode());
        Assert::assertTrue($response->isRedirect('/'));
    }

    /** @test */
    public function it_returns_a_list_of_category_labels_filtered_by_codes(): void
    {
        $this->loginAs('Julia');

        $this->callGetRouteWithQueryParam(
            'akeneo_identifier_generator_get_category_labels',
            ['categoryCodes' => ['categoryA', 'master_china', 'categoryB', 'unknown']]
        );
        $response = $this->client->getResponse();

        $expected = [
            'categoryA' => 'Category A',
            'master_china' => '[master_china]',
            'categoryB' => '[categoryB]',
        ];

        Assert::assertEquals($expected, \json_decode($response->getContent(), true));
    }
}
