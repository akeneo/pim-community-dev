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

namespace Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\AttributeOptionsMapping;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class GetAttributeOptionsMappingByAttributeAndFamilyHandler
{
    /** @var DataProviderInterface */
    private $dataProvider;

    /** @var IdentifiableObjectRepositoryInterface */
    private $familyRepository;

    /**
     * @param DataProviderFactory $dataProviderFactory
     * @param IdentifiableObjectRepositoryInterface $familyRepository
     */
    public function __construct(
        DataProviderFactory $dataProviderFactory,
        IdentifiableObjectRepositoryInterface $familyRepository
    ) {
        $this->dataProvider = $dataProviderFactory->create();
        $this->familyRepository = $familyRepository;
    }

    /**
     * @param GetAttributeOptionsMappingByAttributeAndFamilyQuery $query
     *
     * @return AttributeOptionsMapping
     */
    public function handle(GetAttributeOptionsMappingByAttributeAndFamilyQuery $query): AttributeOptionsMapping
    {
        if (!$this->familyRepository->findOneByIdentifier($query->familyCode()) instanceof FamilyInterface) {
            throw new \InvalidArgumentException(
                sprintf('Family "%s" does not exist', $query->familyCode())
            );
        }

        return $this->dataProvider->getAttributeOptionsMapping($query->familyCode(), $query->franklinAttributeId());
    }
}
