<?php

namespace Pim\Bundle\ProductBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Doctrine\ORM\EntityManager;

/**
 * Transform imported product data into a bindable data to the product form
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TransformImportedProductDataSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManager $entityManager
     */
    protected $entityManager;

    /**
     * @var bool $productEnabled
     */
    protected $productEnabled;

    /**
     * @var string $familyKey The data key which reprensents the family
     **/
    protected $familyKey;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Set wether or not the product should be enabled
     *
     * @param bool $productEnabled
     *
     * @return TransformImportedProductDataSubscriber
     */
    public function setProductEnabled($productEnabled)
    {
        $this->productEnabled = $productEnabled;

        return $this;
    }

    /**
     * Set the family key
     *
     * @param string $familyKey
     *
     * @return TransformImportedProductDataSubscriber
     */
    public function setFamilyKey($familyKey)
    {
        $this->familyKey = $familyKey;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SUBMIT => 'preSubmit'
        );
    }

    /**
     * Transform the imported product data to allow binding them to the form
     *
     * @param FormEvent $event
     *
     * @return null
     */
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();

        $dataToSubmit = array();
        $dataToSubmit = array_merge($this->getProductEnabledData(), $dataToSubmit);
        $dataToSubmit = array_merge($this->getFamilyData($data), $dataToSubmit);

        $event->setData($dataToSubmit);
    }

    /**
     * Return form data to set product enabling (empty array if we don't know)
     *
     * @return array
     */
    private function getProductEnabledData()
    {
        if (null !== $this->productEnabled) {
            return array('enabled' => $this->productEnabled);
        }

        return array();
    }

    /**
     * Return form data to set product family (empty array if we don't know)
     *
     * @return array
     */
    private function getFamilyData(array $data)
    {
        if (null !== $familyId = $this->getFamilyId($data)) {
            return array('family' => $familyId);
        }

        return array();
    }


    /**
     * Get a family id
     *
     * @param array $data The submitted data
     *
     * @return int|null null if the familyKey wasn't sent in the data or the family code doesn't exist
     */
    private function getFamilyId(array $data)
    {
        if (!array_key_exists($this->familyKey, $data)) {
            // TODO Warn that the family could not be determined
            return null;
        }
        if ($family = $this->getFamily($data[$this->familyKey])) {
            return $family->getId();
        }

        // TODO Warn that the family code does not exist
    }

    /**
     * Get a family by code
     *
     * @param string $code
     *
     * @return Family|null
     */
    private function getFamily($code)
    {
        return $this->entityManager
            ->getRepository('PimProductBundle:Family')
            ->findOneBy(array('code' => $code));
    }
}
