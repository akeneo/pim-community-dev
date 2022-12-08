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

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use AkeneoTest\Pim\Enrichment\Integration\Completeness\AbstractCompletenessTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

final class CompletenessOnNonGrantedAttributesIntegration extends AbstractCompletenessTestCase
{
    public function test_it_takes_in_account_non_granted_values_in_completeness(): void
    {
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('ecommerce');
        $attributeGroup = $this->createAttributeGroup('non_granted_group');

        $textAttribute = $this->createAttribute('a_text', AttributeTypes::TEXT);
        $textRequirement = $this->get('pim_catalog.factory.attribute_requirement')
            ->createAttributeRequirement($textAttribute, $channel, true);
        $nonGrantedTextAttribute = $this->createAttribute('non_granted_text', AttributeTypes::TEXT, false, false, [], $attributeGroup);
        $nonGrantedTextRequirement = $this->get('pim_catalog.factory.attribute_requirement')
            ->createAttributeRequirement($nonGrantedTextAttribute, $channel, true);

        $family = $this->findOrCreateFamily('the_family');
        $family->addAttribute($textAttribute);
        $family->addAttributeRequirement($textRequirement);
        $family->addAttribute($nonGrantedTextAttribute);
        $family->addAttributeRequirement($nonGrantedTextRequirement);
        $this->get('pim_catalog.saver.family')->save($family);

        $this->createProductWithStandardValues(
            'another_product',
            [
                new SetFamily($family->getCode()),
                new SetTextValue('a_text', null, null, 'a text'),
                new SetTextValue('non_granted_text', null, null, 'another text'),
            ]
        );

        $userGroups = $this->get('pim_user.repository.group')->findAll();
        $this->get('pimee_security.repository.attribute_group_access')->revokeAccess($attributeGroup, $userGroups);

        $this->logAs('mary');
        $this->get('pim_connector.doctrine.cache_clearer')->clear();

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('another_product');
        /** @var ProductCompletenessWithMissingAttributeCodesCollection $completenesses */
        $completenesses = $this->get('pim_catalog.completeness.missing_required_attributes_calculator')
            ->fromEntityWithFamily($product);

        $this->assertSame(1, $completenesses->count());
        $completenessForChannelAndLocale = $completenesses->getCompletenessForChannelAndLocale('ecommerce', 'en_US');
        self::assertNotNull($completenessForChannelAndLocale);
        self::assertSame(100, $completenessForChannelAndLocale->ratio());
        self::assertSame(3, $completenessForChannelAndLocale->requiredCount());
        self::assertCount(0, $completenessForChannelAndLocale->missingAttributeCodes());
    }

    private function logAs(string $username): void
    {
        $user = $this->get('pim_user.repository.user')->findOneBy(['username' => $username]);

        $token = new UsernamePasswordToken($user, 'main', ['ROLE_USER']);
        $this->get('security.token_storage')->setToken($token);
    }

    private function createAttributeGroup(string $code): AttributeGroup
    {
        $attributeGroup = new AttributeGroup();
        $attributeGroup->setCode($code);
        $attributeGroup->setCreated(new \DateTime());

        $this->get('pim_catalog.saver.attribute_group')->save($attributeGroup);

        return $this->get('pim_catalog.repository.attribute_group')->findOneByIdentifier($code);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAdminUser();
        $user = $this->get('pim_user.factory.user')->create();
        $user->setUsername('mary');
        $user->setPlainPassword('mary');
        $user->setEmail('mary@example.com');
        $user->setSalt('E1F53135E559C253');
        $user->setFirstName('Mary');
        $user->setLastName('Mary');

        $this->get('pim_user.manager')->updatePassword($user);

        $userRole = $this->get('pim_user.repository.role')->findOneByIdentifier('ROLE_USER');
        if (null !== $userRole) {
            $user->addRole($userRole);
        }

        $group = $this->get('pim_user.repository.group')->findOneByIdentifier('All');
        if (null !== $group) {
            $user->removeGroup($group);
        }

        $group = $this->get('pim_user.repository.group')->findOneByIdentifier('IT support');
        if (null !== $group) {
            $user->addGroup($group);
        }

        $this->get('validator')->validate($user);
        $this->get('pim_user.saver.user')->save($user);
    }
}
