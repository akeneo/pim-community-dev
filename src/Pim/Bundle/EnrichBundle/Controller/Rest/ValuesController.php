<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Enrich\Converter\ConverterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Product template controller controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValuesController
{
    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var UserContext */
    protected $userContext;

    /** @var ConverterInterface */
    protected $productValueConverter;

    /** @var AttributeConverterInterface */
    protected $localizedConverter;

    /** @var ObjectUpdaterInterface */
    protected $productUpdater;

    /** @var ValidatorInterface */
    protected $productValidator;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var NormalizerInterface */
    protected $constraintViolationNormalizer;

    /**
     * @param ProductBuilderInterface      $productBuilder
     * @param UserContext                  $userContext
     * @param ConverterInterface           $productValueConverter
     * @param AttributeConverterInterface  $localizedConverter
     * @param ObjectUpdaterInterface       $productUpdater
     * @param ValidatorInterface           $productValidator
     * @param AttributeRepositoryInterface $attributeRepository
     * @param NormalizerInterface          $constraintViolationNormalizer
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        UserContext $userContext,
        ConverterInterface $productValueConverter,
        AttributeConverterInterface $localizedConverter,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
        AttributeRepositoryInterface $attributeRepository,
        NormalizerInterface $constraintViolationNormalizer
    ) {
        $this->productBuilder                = $productBuilder;
        $this->userContext                   = $userContext;
        $this->productValueConverter         = $productValueConverter;
        $this->localizedConverter            = $localizedConverter;
        $this->productUpdater                = $productUpdater;
        $this->productValidator              = $productValidator;
        $this->attributeRepository           = $attributeRepository;
        $this->constraintViolationNormalizer = $constraintViolationNormalizer;
    }

    /**
     * Return the validation error for the product template
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function validateAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $locale = $this->userContext->getUiLocale()->getCode();
        $data   = $this->productValueConverter->convert($data);
        $data   = $this->localizedConverter->convertToDefaultFormats($data, ['locale' => $locale]);

        $product = $this->productBuilder->createProduct('FAKE_SKU_FOR_MASS_EDIT_VALIDATION_' . microtime());
        $this->productUpdater->update($product, ['values' => $data]);
        $violations = $this->productValidator->validate($product);
        $violations->addAll($this->localizedConverter->getViolations());

        $violations = $this->removeIdentifierViolations($violations);

        $normalizedViolations = [];
        foreach ($violations as $violation) {
            $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                $violation,
                'internal_api',
                ['product' => $product]
            );
        }

        return new JsonResponse(['values' => $normalizedViolations]);
    }

    /**
     * Remove all violations related to identifier
     *
     * @param ConstraintViolationListInterface $violations
     *
     * @return ConstraintViolationListInterface
     */
    protected function removeIdentifierViolations(ConstraintViolationListInterface $violations)
    {
        $identifierPath = sprintf('values[%s-<all_channels>-<all_locales>]', $this->attributeRepository->getIdentifierCode());
        foreach ($violations as $offset => $violation) {
            if (0 === strpos($violation->getPropertyPath(), $identifierPath)) {
                $violations->remove($offset);
            }
        }

        return $violations;
    }
}
