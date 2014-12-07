<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Saver;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Resource\Model\BulkSaverInterface;
use Pim\Component\Resource\Model\SaverInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Delegating product saver, depending on context it delegates to other savers to deal with drafts or working copies
 *
 * In future version we'll re-work this part to have a more explicit way of saving working copies and drafts
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class DelegatingProductSaver implements SaverInterface, BulkSaverInterface
{
    /** @var ProductWorkingCopySaver */
    protected $workingCopySaver;

    /** @var ProductDraftSaver */
    protected $draftSaver;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var SecurityContextInterface */
    protected $securityContext;

    /**
     * @param ProductWorkingCopySaver $workingCopySaver
     * @param ProductDraftSaver $draftSaver
     * @param ObjectManager $objectManager
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(
        ProductWorkingCopySaver $workingCopySaver,
        ProductDraftSaver $draftSaver,
        ObjectManager $objectManager,
        SecurityContextInterface $securityContext

    ) {
        $this->workingCopySaver = $workingCopySaver;
        $this->draftSaver = $draftSaver;
        $this->objectManager = $objectManager;
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    public function save($product, array $options = [])
    {
        if (!$product instanceof ProductInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a Pim\Bundle\CatalogBundle\Model\ProductInterface, "%s" provided',
                    ClassUtils::getClass($product)
                )
            );
        }

        $options = $this->resolveOptions($options);

        if (null === $product->getId()) {
            $isOwner = true;
        } else {
            try {
                $isOwner = $this->securityContext->isGranted(Attributes::OWN, $product);
            } catch (AuthenticationCredentialsNotFoundException $e) {
                // We are probably on a CLI context
                $isOwner = true;
            }
        }

        if ($isOwner || $options['bypass_product_draft'] || !$this->objectManager->contains($product)) {
            $this->workingCopySaver->save($product, $options);

        } else {
            $this->draftSaver->save($product, $options);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $products, array $options = [])
    {
        if (empty($products)) {
            return;
        }

        $allOptions = $this->resolveOptions($options);

        if (true === $allOptions['bypass_product_draft']) {
            $this->workingCopySaver->saveAll($products, $options);

        } else {
            $itemOptions = $allOptions;
            $itemOptions['flush'] = false;

            foreach ($products as $product) {
                $this->save($product, $itemOptions);
            }

            if (true === $allOptions['flush']) {
                $this->objectManager->flush();
            }
        }
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function resolveOptions(array $options)
    {
        // TODO : extract the resolver part that should be shared by savers
        $resolver = new OptionsResolver();

        // TODO : default options are not the same for single and bulk save !!
        $resolver->setDefaults(
            [
                'recalculate' => true,
                'flush' => true,
                'schedule' => true,
                'bypass_product_draft' => false
            ]
        );
        $resolver->setAllowedTypes(
            [
                'recalculate' => 'bool',
                'flush' => 'bool',
                'schedule' => 'bool',
                'bypass_product_draft' => 'bool'
            ]
        );
        $options = $resolver->resolve($options);

        return $options;
    }
}