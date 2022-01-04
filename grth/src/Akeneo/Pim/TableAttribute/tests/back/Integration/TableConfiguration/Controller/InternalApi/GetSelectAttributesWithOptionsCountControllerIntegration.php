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
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use Akeneo\Test\Pim\TableAttribute\Integration\TableConfiguration\Controller\ControllerIntegrationTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SecurityBundle\Model\AclPermission;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

final class GetSelectAttributesWithOptionsCountControllerIntegration extends ControllerIntegrationTestCase
{
    private WebClientHelper $webClientHelper;

    /** @test */
    public function it_is_forbidden_when_user_is_not_log_in(): void
    {
        $this->webClientHelper->callApiRoute(
            $this->client,
            'pim_table_attribute_get_select_attributes_with_options_count'
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    /** @test */
    public function it_returns_the_options_of_a_select_column(): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($this->client, 'julia');
        $this->webClientHelper->callApiRoute(
            $this->client,
            'pim_table_attribute_get_select_attributes_with_options_count'
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());
        Assert::assertEqualsCanonicalizing(
            [
                'brand' => [
                    'options_count' => 10,
                    'labels' => ['en_US' => 'Brand label', 'fr_FR' => 'Marque label']
                ],
                'color' => [
                    'options_count' => 5,
                    'labels' => ['en_US' => 'Color label', 'fr_FR' => 'Couleur label']
                ],
            ],
            json_decode($response->getContent(), true)
        );
    }

    /** @test */
    public function it_returns_the_options_of_a_select_column_with_limit_and_page_number(): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($this->client, 'julia');
        $this->webClientHelper->callApiRoute(
            $this->client,
            'pim_table_attribute_get_select_attributes_with_options_count',
            [],
            'GET',
            [
                'limit' => 1,
                'page' => 1,
            ]
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());
        Assert::assertEqualsCanonicalizing(
            [
                'brand' => [
                    'options_count' => 10,
                    'labels' => ['en_US' => 'Brand label', 'fr_FR' => 'Marque label']
                ],
            ],
            json_decode($response->getContent(), true)
        );

        $this->webClientHelper->callApiRoute(
            $this->client,
            'pim_table_attribute_get_select_attributes_with_options_count',
            [],
            'GET',
            [
                'limit' => 1,
                'page' => 2,
            ]
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());
        Assert::assertEqualsCanonicalizing(
            [
                'color' => [
                    'options_count' => 5,
                    'labels' => ['en_US' => 'Color label', 'fr_FR' => 'Couleur label']
                ],
            ],
            json_decode($response->getContent(), true)
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');

        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, [
            'code' => 'brand',
            'type' => AttributeTypes::OPTION_SIMPLE_SELECT,
            'group' => 'other',
            'localizable' => false,
            'scopable' => false,
            'labels' => ['en_US' => 'Brand label', 'fr_FR' => 'Marque label'],
        ]);
        $violations = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $violations, \sprintf('The attribute is not valid: %s', $violations));
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        for ($i=0; $i<10; $i++) {
            $attributeOption = $this->get('pim_catalog.factory.attribute_option')->create();
            $this->get('pim_catalog.updater.attribute_option')->update($attributeOption, [
                'code' => 'brand_'.$i,
                'attribute' => 'brand',
                'labels' => ['en_US' => 'Brand_'.$i, 'fr_FR' => 'Marque_'.$i]
            ]);
            Assert::assertCount(0, $violations, \sprintf('The attribute option is not valid: %s', $violations));
            $this->get('pim_catalog.saver.attribute_option')->save($attributeOption);
        }

        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, [
            'code' => 'color',
            'type' => AttributeTypes::OPTION_MULTI_SELECT,
            'group' => 'other',
            'localizable' => false,
            'scopable' => false,
            'labels' => ['en_US' => 'Color label', 'fr_FR' => 'Couleur label'],
        ]);
        $violations = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $violations, \sprintf('The attribute is not valid: %s', $violations));
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        for ($i=0; $i<5; $i++) {
            $attributeOption = $this->get('pim_catalog.factory.attribute_option')->create();
            $this->get('pim_catalog.updater.attribute_option')->update($attributeOption, [
                'code' => 'color_'.$i,
                'attribute' => 'color',
                'labels' => ['en_US' => 'Color_'.$i, 'fr_FR' => 'Couleur_'.$i]
            ]);
            Assert::assertCount(0, $violations, \sprintf('The attribute option is not valid: %s', $violations));
            $this->get('pim_catalog.saver.attribute_option')->save($attributeOption);
        }

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
