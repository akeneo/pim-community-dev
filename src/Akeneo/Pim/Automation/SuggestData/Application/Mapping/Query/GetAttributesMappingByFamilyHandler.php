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
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\AttributesMappingResponse;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class GetAttributesMappingByFamilyHandler
{
    /** @var DataProviderInterface */
    private $dataProvider;

    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /**
     * @param DataProviderFactory $dataProviderFactory
     * @param FamilyRepositoryInterface $familyRepository
     */
    public function __construct(DataProviderFactory $dataProviderFactory, FamilyRepositoryInterface $familyRepository)
    {
        $this->dataProvider = $dataProviderFactory->create();
        $this->familyRepository = $familyRepository;
    }

    /**
     * @param GetAttributesMappingByFamilyQuery $query
     *
     * @return AttributesMappingResponse
     */
    public function handle(GetAttributesMappingByFamilyQuery $query): AttributesMappingResponse
    {
        $this->ensureFamilyExists($query->getFamilyCode());

        return $this->dataProvider->getAttributesMapping($query->getFamilyCode());
    }

    /**
     * @param string $familyCode
     */
    private function ensureFamilyExists(string $familyCode): void
    {
        $family = $this->familyRepository->findOneByIdentifier($familyCode);

        if (!$family instanceof FamilyInterface) {
            throw new \InvalidArgumentException(sprintf(
                'The family with code "%s" does not exist',
                $familyCode
            ));
        }
    }
}
