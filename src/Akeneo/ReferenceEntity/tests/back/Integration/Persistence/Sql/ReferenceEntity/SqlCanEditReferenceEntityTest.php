<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\ReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Model\PrincipalIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\CanEditReferenceEntityInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

final class SqlCanEditReferenceEntityTest extends SqlIntegrationTestCase
{
    /** @var CanEditReferenceEntityInterface */
    private $canEditReferenceEntity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->canEditReferenceEntity = $this->get('akeneo.referencentity.infrastructure.persistence.permission.query.can_edit_reference_entity');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_tells_if_a_user_is_allowed_to_edit_a_reference_entity()
    {
        Assert::assertFalse(
            ($this->canEditReferenceEntity)(
                PrincipalIdentifier::fromString('julia'),
                ReferenceEntityIdentifier::fromString('brand')
            )
        );
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }
}
