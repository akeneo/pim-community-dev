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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Structure\Service;

use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Service\ProposalUpsertInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\AddAttributeToFamilyInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\CreateAttributeInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeLabel;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Proposal\ValueObject\ProposalSuggestedData;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal\ProposalUpsert;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Structure\Service\AddAttributeToFamily;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Structure\Service\CreateAttribute;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\FamilyRepository;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver\AttributeSaver;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver\FamilySaver;
use Akeneo\Pim\Structure\Component\Factory\AttributeFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Updater\AttributeUpdater;
use Akeneo\Pim\Structure\Component\Updater\FamilyUpdater;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Builder\EntityWithValuesDraftBuilderInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\EntityWithValuesDraftEvents;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class AddAttributeToFamilySpec extends ObjectBehavior
{
    public function let(
        FamilyUpdater $updater,
        FamilySaver $saver,
        FamilyRepository $repository,
        ValidatorInterface $validator
    ): void {
        $this->beConstructedWith($updater, $saver, $repository, $validator);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(AddAttributeToFamily::class);
    }

    public function it_is_an_implementation_of_add_attribute_to_family(): void
    {
        $this->shouldImplement(AddAttributeToFamilyInterface::class);
    }

    public function it_adds_attribute_to_a_family(
        $updater,
        $saver,
        $repository,
        $validator,
        FamilyInterface $family,
        ConstraintViolationListInterface $violations
    ): void {
        $attributeData = [
            'attributes' => 'Foo_bar',
        ];

        $repository->findOneByIdentifier('bar')->willReturn($family);
        //$family->getAttributeCodes()->willReturn()

        $this->addAttributeToFamily(
            AttributeCode::fromString('Foo'),
            new FamilyCode('bar')
        )->shouldReturn(null);
    }
}
