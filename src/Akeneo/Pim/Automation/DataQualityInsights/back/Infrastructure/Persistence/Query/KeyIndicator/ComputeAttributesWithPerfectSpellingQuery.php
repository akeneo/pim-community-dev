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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\KeyIndicator\AttributesWithPerfectSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\KeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\KeyIndicator\ComputeStructureKeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetFamiliesByCategoryCodesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\KeyIndicatorCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\Quality;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Doctrine\DBAL\Connection;

final class ComputeAttributesWithPerfectSpellingQuery implements ComputeStructureKeyIndicator
{
    private Connection $dbConnection;

    private GetFamiliesByCategoryCodesQueryInterface $getFamiliesByCategoryCodesQuery;

    private CategoryRepositoryInterface $categoryRepository;

    public function __construct(
        Connection $dbConnection,
        GetFamiliesByCategoryCodesQueryInterface $getFamiliesByCategoryCodesQuery,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->dbConnection = $dbConnection;
        $this->getFamiliesByCategoryCodesQuery = $getFamiliesByCategoryCodesQuery;
        $this->categoryRepository = $categoryRepository;
    }

    public function computeByLocale(LocaleCode $localeCode): KeyIndicator
    {
        $query = <<<SQL
SELECT JSON_OBJECTAGG(quality, nb_attributes) FROM (
    SELECT quality, COUNT(*) AS nb_attributes
    FROM pimee_dqi_attribute_locale_quality
    WHERE locale = :locale AND quality IN (:goodAndToImprove)
    GROUP BY quality
) quality_by_locale
SQL;

        $result = $this->dbConnection->executeQuery(
            $query,
            [
                'locale' => $localeCode,
                'goodAndToImprove' => [Quality::GOOD, Quality::TO_IMPROVE],
            ],
            [
                'goodAndToImprove' => Connection::PARAM_STR_ARRAY,
            ]
        )->fetchColumn();

        $result = (false !== $result && null !== $result) ? json_decode($result, true, 512, JSON_THROW_ON_ERROR) : [];

        return new KeyIndicator(
            new KeyIndicatorCode(AttributesWithPerfectSpelling::CODE),
            $result[Quality::GOOD] ?? 0,
            $result[Quality::TO_IMPROVE] ?? 0
        );
    }

    public function computeByLocaleAndFamily(LocaleCode $localeCode, FamilyCode $familyCode): KeyIndicator
    {
        return $this->computeByFamilies($localeCode, [$familyCode]);
    }

    public function computeByLocaleAndCategory(LocaleCode $localeCode, CategoryCode $categoryCode): KeyIndicator
    {
        $categoryCode = strval($categoryCode);
        $category = $this->categoryRepository->findOneByIdentifier($categoryCode);
        $categoryChildren = $category instanceof CategoryInterface ? $this->categoryRepository->getAllChildrenCodes($category) : [];
        $families = $this->getFamiliesByCategoryCodesQuery->execute(array_merge([$categoryCode], $categoryChildren));

        if (empty($families)) {
            return new KeyIndicator(new KeyIndicatorCode(AttributesWithPerfectSpelling::CODE), 0, 0);
        }

        return $this->computeByFamilies($localeCode, $families);
    }

    private function computeByFamilies(LocaleCode $localeCode, array $families): KeyIndicator
    {
        $query = <<<SQL
SELECT JSON_OBJECTAGG(quality, nb_attributes) FROM (
    SELECT quality, COUNT(DISTINCT attribute.id) AS nb_attributes
    FROM pimee_dqi_attribute_locale_quality AS attribute_quality
        INNER JOIN pim_catalog_attribute AS attribute ON attribute.code = attribute_quality.attribute_code
        INNER JOIN pim_catalog_family_attribute AS family_attribute ON family_attribute.attribute_id = attribute.id
        INNER JOIN pim_catalog_family AS family ON family.id = family_attribute.family_id
    WHERE locale = :locale AND quality IN (:goodAndToImprove)
        AND family.code IN (:families)
    GROUP BY quality
) quality_by_locale
SQL;

        $result = $this->dbConnection->executeQuery(
            $query,
            [
                'locale' => $localeCode,
                'goodAndToImprove' => [Quality::GOOD, Quality::TO_IMPROVE],
                'families' => $families,
            ],
            [
                'goodAndToImprove' => Connection::PARAM_STR_ARRAY,
                'families' => Connection::PARAM_STR_ARRAY,
            ]
        )->fetchColumn();

        $result = (false !== $result && null !== $result) ? json_decode($result, true, 512, JSON_THROW_ON_ERROR) : [];

        return new KeyIndicator(
            new KeyIndicatorCode(AttributesWithPerfectSpelling::CODE),
            $result[Quality::GOOD] ?? 0,
            $result[Quality::TO_IMPROVE] ?? 0,
            ['impactedFamilies' => array_map(fn ($family) => strval($family), $families)]
        );
    }
}
