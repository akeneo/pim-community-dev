<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\TableAttribute\Integration\TableConfiguration\Controller\InternalApi;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectOptionCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\WriteSelectOptionCollection;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use Akeneo\Test\Pim\TableAttribute\EndToEnd\ControllerEndToEndTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SecurityBundle\Model\AclPermission;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

final class GetSelectOptionsControllerEndToEnd extends ControllerEndToEndTestCase
{
    private WebClientHelper $webClientHelper;

    /** @test */
    public function it_is_forbidden_when_user_is_not_log_in(): void
    {
        $this->webClientHelper->callApiRoute(
            $this->client,
            'pim_table_attribute_get_select_options',
            ['attributeCode' => 'nutrition', 'columnCode' => 'ingredients']
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    /** @test */
    public function it_returns_the_options_of_a_select_column(): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('julia', $this->client);
        $this->webClientHelper->callApiRoute(
            $this->client,
            'pim_table_attribute_get_select_options',
            ['attributeCode' => 'nutrition', 'columnCode' => 'ingredients']
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    /** @test */
    public function it_returns_the_options_of_a_select_column_case_insensitive(): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('julia', $this->client);
        $this->webClientHelper->callApiRoute(
            $this->client,
            'pim_table_attribute_get_select_options',
            ['attributeCode' => 'NUtrition', 'columnCode' => 'INgredients']
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    /** @test */
    public function it_returns_404_when_attribute_is_unknown(): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('julia', $this->client);
        $this->webClientHelper->callApiRoute(
            $this->client,
            'pim_table_attribute_get_select_options',
            ['attributeCode' => 'unknown', 'columnCode' => 'ingredients']
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /** @test */
    public function it_returns_bad_request_when_attribute_is_not_a_table(): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('julia', $this->client);
        $this->webClientHelper->callApiRoute(
            $this->client,
            'pim_table_attribute_get_select_options',
            ['attributeCode' => 'a_text', 'columnCode' => 'ingredients']
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    /** @test */
    public function it_returns_404_when_column_is_unknown(): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('julia', $this->client);
        $this->webClientHelper->callApiRoute(
            $this->client,
            'pim_table_attribute_get_select_options',
            ['attributeCode' => 'nutrition', 'columnCode' => 'unknown']
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /** @test */
    public function it_returns_422_when_column_is_not_a_select(): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('julia', $this->client);
        $this->webClientHelper->callApiRoute(
            $this->client,
            'pim_table_attribute_get_select_options',
            ['attributeCode' => 'nutrition', 'columnCode' => 'quantity']
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');

        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, [
            'code' => 'nutrition',
            'type' => AttributeTypes::TABLE,
            'group' => 'other',
            'localizable' => false,
            'scopable' => false,
            'table_configuration' => [
                [
                    'code' => 'ingredients',
                    'data_type' => 'select',
                    'labels' => [
                        'en_US' => 'Ingredients',
                    ],
                ],
                [
                    'code' => 'quantity',
                    'data_type' => 'number',
                    'labels' => [
                        'en_US' => 'Quantity',
                    ],
                ],
            ]
        ]);
        $violations = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $violations, \sprintf('The attribute is not valid: %s', $violations));
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, [
            'code' => 'a_text',
            'type' => AttributeTypes::TEXT,
            'group' => 'other',
            'localizable' => false,
            'scopable' => false,
        ]);
        $violations = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $violations, \sprintf('The attribute is not valid: %s', $violations));
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        $optionCollection = SelectOptionCollection::fromNormalized([
            ['code' => 'salt', 'labels' => ['en_US' => 'Salt', 'fr_FR' => 'Sel']],
            ['code' => 'sugar', 'labels' => ['en_US' => 'Sugar', 'fr_FR' => 'Sucre']],
        ]);
        $violations = $this->get('validator')->validate($optionCollection);
        Assert::assertCount(0, $violations, \sprintf('The option collection is not valid: %s', $violations));

        $this->get(SelectOptionCollectionRepository::class)->save(
            'nutrition',
            ColumnCode::fromString('ingredients'),
            WriteSelectOptionCollection::fromReadSelectOptionCollection($optionCollection)
        );

        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    private function disableAcl(string $aclId) : void
    {
        $aclManager = $this->get('oro_security.acl.manager');
        $roles = $this->get('pim_user.repository.role')->findAll();

        foreach ($roles as $role) {
            $privilege = new AclPrivilege();
            $identity = new AclPrivilegeIdentity($aclId);
            $privilege
                ->setIdentity($identity)
                ->addPermission(new AclPermission('EXECUTE', 0));

            $aclManager->getPrivilegeRepository()
                ->savePrivileges($aclManager->getSid($role), new ArrayCollection([$privilege]));
        }

        $aclManager->flush();
        $aclManager->clearCache();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
