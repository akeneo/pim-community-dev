<?php

namespace PimEnterprise\Bundle\EnrichBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Manager\AttributeManager;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\EnrichBundle\Form\Handler\AttributeHandler as BaseAttributeHandler;
use PimEnterprise\Bundle\WorkflowBundle\Exception\PublishedProductConsistencyException;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;

/**
 * Override attribute handler
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeHandler extends BaseAttributeHandler
{
    /** @var PublishedProductRepositoryInterface */
    protected $publishedRepository;

    /**
     * @param FormInterface                       $form
     * @param Request                             $request
     * @param ObjectManager                       $manager
     * @param AttributeManager                    $attributeManager
     * @param PublishedProductRepositoryInterface $publishedRepository
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        ObjectManager $manager,
        AttributeManager $attributeManager,
        PublishedProductRepositoryInterface $publishedRepository
    ) {
        parent::__construct($form, $request, $manager, $attributeManager);

        $this->publishedRepository = $publishedRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @throws PublishedProductConsistencyException
     */
    public function process(AbstractAttribute $entity)
    {
        $this->addMissingOptionValues($entity);
        $this->form->setData($entity);

        if ($this->request->isMethod('POST')) {
            $oldOptions = clone $entity->getOptions();
            $this->form->submit($this->request);

            // Check if attribute options have been already published
            if (false === $this->checkAttributeOptionsRemovable($entity, $oldOptions)) {
                throw new PublishedProductConsistencyException(
                    "Impossible to remove an option that has been published in a product",
                    0,
                    null,
                    'pim_enrich_attribute_edit',
                    ['id' => $entity->getId()]
                );
            }

            if ($this->form->isValid()) {
                $this->onSuccess($entity, $oldOptions);

                return true;
            }
        }

        return false;
    }

    /**
     * Forbid to remove an option from an attribute that have been published
     *
     * @param AbstractAttribute $entity
     * @param Collection        $oldOptions
     *
     * @return boolean
     */
    protected function checkAttributeOptionsRemovable(AbstractAttribute $entity, Collection $oldOptions)
    {
        foreach ($oldOptions as $oldOption) {
            if (false === $entity->getOptions()->contains($oldOption)) {
                if ($this->publishedRepository->countPublishedProductsForAttributeOption($oldOption) > 0) {
                    return false;
                }
            }
        }

        return true;
    }
}
