<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Query;

use Akeneo\Category\API\Query\Category;
use Akeneo\Category\API\Query\GetCategory;
use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Component\Model\CategoryTranslationInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class OrmGetCategory implements GetCategory
{
    public function __construct(
        private CategoryRepositoryInterface           $categoryRepository,
        private IdentifiableObjectRepositoryInterface $localeRepository,
    ) {
    }

    public function byCode(string $code): ?Category
    {
        /** @var CategoryInterface $categoryEntity */
        $categoryEntity = $this->categoryRepository->findOneByIdentifier($code);

        if (null === $categoryEntity) {
            return null;
        }

        return new Category(
            $categoryEntity->getCode(),
            $categoryEntity->getParent()?->getCode(),
            \DateTimeImmutable::createFromInterface($categoryEntity->getUpdated()),
            $this->getLabels($categoryEntity),
        );
    }

    private function getLabels(CategoryInterface $categoryEntity): array
    {
        $labels = [];
        foreach ($categoryEntity->getTranslations() as $translation) {
            Assert::isInstanceOf($translation, CategoryTranslationInterface::class);
            $locale = $this->localeRepository->findOneByIdentifier($translation->getLocale());
            if (null === $locale || !$locale->isActivated()) {
                continue;
            }

            $labels[$translation->getLocale()] = $translation->getLabel();
        }

        return $labels;
    }
}
