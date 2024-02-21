<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator;

use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @author    Mathias Métayer <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ValidateCategories
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $categoryRepository;

    public function __construct(IdentifiableObjectRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @throws InvalidQueryException
     */
    public function validate(array $search): void
    {
        if (!isset($search['categories'])) {
            return;
        }

        $categories = $search['categories'];
        if (!is_array($categories)) {
            throw new InvalidQueryException(
                sprintf('Search query parameter "categories" has to be an array, "%s" given.', gettype($categories))
            );
        }

        $errors = [];
        foreach ($categories as $category) {
            foreach ($category['value'] as $categoryCode) {
                $categoryCode = trim($categoryCode);
                if (null === $this->categoryRepository->findOneByIdentifier($categoryCode)) {
                    $errors[] = $categoryCode;
                }
            }
        }

        if (!empty($errors)) {
            $plural = count($errors) > 1 ? 'Categories "%s" do not exist.' : 'Category "%s" does not exist.';
            throw new InvalidQueryException(sprintf($plural, implode(', ', $errors)));
        }
    }
}
