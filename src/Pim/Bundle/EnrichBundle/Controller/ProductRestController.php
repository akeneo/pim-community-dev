<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Product controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRestController
{
    /** @var ProductManager */
    protected $productManager;

    /** @var ProductUpdaterInterface */
    protected $productUpdater;

    /** @var SaverInterface */
    protected $productSaver;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var UserContext */
    protected $userContext;

    /**
     * @param ProductManager          $productManager
     * @param ProductUpdaterInterface $productUpdater
     * @param SaverInterface          $productSaver
     * @param NormalizerInterface     $normalizer
     * @param ValidatorInterface      $validator
     * @param UserContext             $userContext
     */
    public function __construct(
        ProductManager $productManager,
        ProductUpdaterInterface $productUpdater,
        SaverInterface $productSaver,
        NormalizerInterface $normalizer,
        ValidatorInterface $validator,
        UserContext $userContext
    ) {
        $this->productManager = $productManager;
        $this->productUpdater = $productUpdater;
        $this->productSaver   = $productSaver;
        $this->normalizer     = $normalizer;
        $this->validator      = $validator;
        $this->userContext    = $userContext;
    }

    /**
     * Edit product
     *
     * @param int $id
     *
     * @return array
     *
     * @Template("PimEnrichBundle:Product:edit.html.twig")
     * @AclAncestor("pim_enrich_product_index")
     */
    public function editAction($id)
    {
        return [
            'productId' => $id
        ];
    }

    /**
     * @param string $id
     *
     * @return JsonResponse
     */
    public function getAction($id)
    {
        $product  = $this->findProductOr404($id);
        $channels = array_keys($this->userContext->getChannelChoicesWithUserChannel());
        $locales  = $this->userContext->getUserLocaleCodes();

        return new JsonResponse(
            $this->normalizer->normalize(
                $product,
                'internal_api',
                [
                    'locales'     => $locales,
                    'channels'    => $channels,
                    'filter_type' => 'pim:internal_api:product_value:view'
                ]
            )
        );
    }

    /**
     * @param Request $request
     * @param string  $id
     *
     * @return JsonResponse
     */
    public function postAction(Request $request, $id)
    {
        $product = $this->findProductOr404($id);

        $data = json_decode($request->getContent(), true);

        foreach ($data as $item => $itemData) {
            if ('values' === $item) {
                foreach ($itemData as $attributeCode => $values) {
                    foreach ($values as $value) {
                        $this->productUpdater->setData(
                            $product,
                            $attributeCode,
                            $value['value'],
                            [
                                'locale' => $value['locale'],
                                'scope'  => $value['scope']
                            ]
                        );
                    }
                }
            } else {
                $this->productUpdater->setData(
                    $product,
                    $item,
                    $itemData
                );
            }
        }

        $violations = $this->validator->validate($product);
        if (0 === $violations->count()) {
            $violations = $this->validator->validate($product->getValues());
        }

        if (0 === $violations->count()) {
            $this->productSaver->save($product);

            return new JsonResponse($this->normalizer->normalize($product, 'internal_api'));
        } else {
            $errors = [];
            foreach ($violations as $violation) {
                $path = $violation->getPropertyPath();
                if (0 === strpos($path, 'values')) {
                    $codeStart  = strpos($path, '[') + 1;
                    $codeLength = strpos($path, ']') - $codeStart;

                    $valueIndex = substr($path, $codeStart, $codeLength);
                    $value = $product->getValues()[$valueIndex];

                    $errors['values'][$value->getAttribute()->getCode()][] = [
                        'attribute'     => $value->getAttribute()->getCode(),
                        'locale'        => $value->getLocale(),
                        'scope'         => $value->getScope(),
                        'message'       => $violation->getMessage(),
                        'invalid_value' => $violation->getInvalidValue()
                    ];
                } else {
                    $errors[$path] = [
                        'message'       => $violation->getMessage(),
                        'invalid_value' => $violation->getInvalidValue()
                    ];
                }
            }

            return new JsonResponse($errors, 400);
        }
    }

    /**
     * Find a product by its id or return a 404 response
     *
     * @param string $id the product id
     *
     * @return ProductInterface
     *
     * @throws NotFoundHttpException
     */
    protected function findProductOr404($id)
    {
        // Use repository for that !
        $product = $this->productManager->find($id);

        if (null === $product) {
            throw new NotFoundHttpException(
                sprintf('Product with id %s could not be found.', (string) $id)
            );
        }

        return $product;
    }
}
