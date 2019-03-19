<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Bundle\Sql\LruArrayAttributeRepository;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
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

    /** @var LruArrayAttributeRepository */
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
     * @param LruArrayAttributeRepository  $attributeRepository
     * @param NormalizerInterface          $constraintViolationNormalizer
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        UserContext $userContext,
        ConverterInterface $productValueConverter,
        AttributeConverterInterface $localizedConverter,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
        LruArrayAttributeRepository $attributeRepository,
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
