<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

    /** @var FilterInterface */
    protected $unchangedValuesFilter;

    public function __construct(
        ProductBuilderInterface $productBuilder,
        UserContext $userContext,
        ConverterInterface $productValueConverter,
        AttributeConverterInterface $localizedConverter,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
        AttributeRepositoryInterface $attributeRepository,
        NormalizerInterface $constraintViolationNormalizer,
        FilterInterface $emptyValuesFilter
    ) {
        $this->productBuilder = $productBuilder;
        $this->userContext = $userContext;
        $this->productValueConverter = $productValueConverter;
        $this->localizedConverter = $localizedConverter;
        $this->productUpdater = $productUpdater;
        $this->productValidator = $productValidator;
        $this->attributeRepository = $attributeRepository;
        $this->constraintViolationNormalizer = $constraintViolationNormalizer;
        $this->unchangedValuesFilter = $emptyValuesFilter;
    }

    /**
     * Return the validation error for the product template
     *
     * @param Request $request
     *
     * @return Response
     */
    public function validateAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $data = json_decode($request->getContent(), true);

        $locale = $this->userContext->getUiLocale()->getCode();
        $data   = $this->productValueConverter->convert($data);
        $data   = $this->localizedConverter->convertToDefaultFormats($data, ['locale' => $locale]);
        $data = ['values' => $data];

        $product = $this->productBuilder->createProduct('FAKE_SKU_FOR_MASS_EDIT_VALIDATION_' . microtime());
        $data = $this->unchangedValuesFilter->filter($product, $data);

        $this->productUpdater->update($product, $data);
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
