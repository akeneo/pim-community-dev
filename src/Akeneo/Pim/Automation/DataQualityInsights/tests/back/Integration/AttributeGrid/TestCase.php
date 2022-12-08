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

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\AttributeGrid;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\Structure\AttributeLabelsSpellingEvaluatedEvent;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\AttributeSpellcheckRepository;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase as IntegrationTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class TestCase extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->get('feature_flags')->enable('data_quality_insights');
        $this->get('feature_flags')->enable('data_quality_insights_all_criteria');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function givenAnAuthenticatedUser(string $username): void
    {
        $user = $this->get('pim_user.factory.user')->create();
        $this->get('pim_user.updater.user')->update($user, [
            'username' => $username,
            'password' => 'qwerty',
            'email' => sprintf('%s@akeneo.com', $username),
        ]);

        $userRole = $this->get('pim_user.repository.role')->findOneByIdentifier('ROLE_CATALOG_MANAGER');
        $user->addRole($userRole);

        $this->get('pim_user.saver.user')->save($user);

        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
    }

    protected function givenAnAttributeWithoutSpellingMistake(string $attributeCode): AttributeInterface
    {
        $attribute = $this->createAttribute($attributeCode);

        $this->updateAttributeSpellcheck(new AttributeSpellcheck(
            new AttributeCode($attributeCode),
            new \DateTimeImmutable(),
            (new SpellcheckResultByLocaleCollection())
                ->add(new LocaleCode('en_US'), SpellCheckResult::good())
        ));

        return $attribute;
    }

    protected function givenAnAttributeWithSpellingMistakes(string $attributeCode): AttributeInterface
    {
        $attribute = $this->createAttribute($attributeCode);

        $this->updateAttributeSpellcheck(new AttributeSpellcheck(
            new AttributeCode($attributeCode),
            new \DateTimeImmutable(),
            (new SpellcheckResultByLocaleCollection())
                ->add(new LocaleCode('en_US'), SpellCheckResult::toImprove())
        ));

        return $attribute;
    }

    protected function givenAnAttributeWithSpellcheckInProgress(string $attributeCode): AttributeInterface
    {
        $attribute = $this->createAttribute($attributeCode);

        $query = <<<SQL
DELETE FROM pimee_dqi_attribute_quality WHERE attribute_code = :attributeCode;
SQL;

        $this->get('database_connection')->executeQuery($query, [
            'attributeCode' => $attributeCode,
        ]);

        return $attribute;
    }

    protected function givenAnAttributeOnWhichSpellcheckIsNotApplicable(string $attributeCode): AttributeInterface
    {
        $attribute = $this->createAttribute($attributeCode);

        $this->updateAttributeSpellcheck(new AttributeSpellcheck(
            new AttributeCode($attributeCode),
            new \DateTimeImmutable(),
            new SpellcheckResultByLocaleCollection()
        ));

        return $attribute;
    }

    protected function createAttribute(string $code): AttributeInterface
    {
        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build([
            'code' => $code,
            'type' => AttributeTypes::TEXT,
            'group' => 'other',
        ], true);

        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }

    private function updateAttributeSpellcheck(AttributeSpellcheck $attributeSpellcheck): void
    {
        $this->get(AttributeSpellcheckRepository::class)->save($attributeSpellcheck);
        $this->get('event_dispatcher')->dispatch(new AttributeLabelsSpellingEvaluatedEvent($attributeSpellcheck));
    }
}
