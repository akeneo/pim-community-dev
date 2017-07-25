<?php

namespace Pim\Bundle\ApiBundle\Checker;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class QueryParametersChecker implements QueryParametersCheckerInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $localeRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $categoryRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     * @param IdentifiableObjectRepositoryInterface $attributeRepository
     * @param IdentifiableObjectRepositoryInterface $categoryRepository
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $categoryRepository
    ) {
        $this->localeRepository = $localeRepository;
        $this->attributeRepository = $attributeRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function checkLocalesParameters(array $localeCodes, ChannelInterface $channel = null)
    {
        $localeCodes = array_map('trim', $localeCodes);
        $errors = [];
        foreach ($localeCodes as $localeCode) {
            $locale = $this->localeRepository->findOneByIdentifier($localeCode);
            if (null === $locale || !$locale->isActivated()) {
                $errors[] = $localeCode;
            }
        }

        if (!empty($errors)) {
            $plural = count($errors) > 1 ?
                'Locales "%s" do not exist or are not activated.' : 'Locale "%s" does not exist or is not activated.';
            throw new UnprocessableEntityHttpException(sprintf($plural, implode(', ', $errors)));
        }

        if (null !== $channel) {
            $diff = array_diff($localeCodes, $channel->getLocaleCodes());
            if ($diff) {
                $plural = sprintf(count($diff) > 1 ? 'Locales "%s" are' : 'Locale "%s" is', implode(', ', $diff));
                throw new UnprocessableEntityHttpException(
                    sprintf('%s not activated for the scope "%s".', $plural, $channel->getCode())
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkAttributesParameters(array $attributeCodes)
    {
        $errors = [];
        foreach ($attributeCodes as $attributeCode) {
            $attributeCode = trim($attributeCode);
            if (null === $this->attributeRepository->findOneByIdentifier($attributeCode)) {
                $errors[] = $attributeCode;
            }
        }

        if (!empty($errors)) {
            $plural = count($errors) > 1 ? 'Attributes "%s" do not exist.' : 'Attribute "%s" does not exist.';
            throw new UnprocessableEntityHttpException(sprintf($plural, implode(', ', $errors)));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkCategoriesParameters(array $categories)
    {
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
            throw new UnprocessableEntityHttpException(sprintf($plural, implode(', ', $errors)));
        }
    }
}
