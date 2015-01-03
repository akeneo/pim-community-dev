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
use Akeneo\Component\Persistence\BulkSaverInterface;
use Akeneo\Component\Persistence\SaverInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Saver\ProductSaver;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Delegating product saver, depending on context it delegates to other savers to deal with drafts or working copies
 *
 * TODO : should implement only SaverInterface ?
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class DelegatingProductSaver implements SaverInterface, BulkSaverInterface
{
    /** @var ProductSaver */
    protected $workingCopySaver;

    /** @var ProductDraftSaver */
    protected $draftSaver;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var SecurityContextInterface */
    protected $securityContext;

    /**
     * @param ProductSaver      $workingCopySaver
     * @param ProductDraftSaver $productDraftSaver
     * @param ObjectManager $objectManager
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(
        ProductSaver $workingCopySaver,
        ProductDraftSaver $draftSaver,
        ObjectManager $objectManager,
        SecurityContextInterface $securityContext

    ) {
        # TODO use interfaces for working copy and draft !!
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

        $options = $this->resolveSaveOptions($options);

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

        $allOptions = $this->resolveSaveAllOptions($options);

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
     * Resolve options for a single save
     *
     * @param array $options
     *
     * @return array
     */
    protected function resolveSaveOptions(array $options)
    {
        $resolver = $this->createOptionsResolver();
        $resolver->setDefaults(
            [
                'flush' => true,
                'recalculate' => true,
                'schedule' => true,
                'bypass_product_draft' => false // TODO : Should be changed
            ]
        );
        $options = $resolver->resolve($options);

        return $options;
    }

    /**
     * Resolve options for a bulk save
     *
     * @param array $options
     *
     * @return array
     */
    protected function resolveSaveAllOptions(array $options)
    {
        $resolver = $this->createOptionsResolver();
        $resolver->setDefaults(
            [
                'flush' => true,
                'recalculate' => false,
                'schedule' => true,
                'bypass_product_draft' => false // TODO : Should be changed
            ]
        );
        $options = $resolver->resolve($options);

        return $options;
    }

    /**
     * @return OptionsResolverInterface
     */
    protected function createOptionsResolver()
    {
        $resolver = new OptionsResolver();
        $resolver->setOptional(['flush', 'recalculate', 'schedule', 'bypass_product_draft']);
        $resolver->setAllowedTypes(
            [
                'flush' => 'bool',
                'recalculate' => 'bool',
                'schedule' => 'bool',
                'bypass_product_draft' => 'bool'
            ]
        );

        return $resolver;
    }
}