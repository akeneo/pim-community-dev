<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Doctrine\CompletenessQueryBuilder;
use Pim\Bundle\CatalogBundle\Entity\AttributeRequirement;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\ProductValueNotBlank;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Manages completeness
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessManager
{
    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * @var CompletenessQueryBuilder
     */
    protected $completenessQB;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var string
     */
    protected $class;

    /**
     * Constructor
     *
     * @param RegistryInterface        $doctrine
     * @param CompletenessQueryBuilder $completenessQB
     * @param ValidatorInterface       $validator
     * @param string                   $class
     */
    public function __construct(
        RegistryInterface $doctrine,
        CompletenessQueryBuilder $completenessQB,
        ValidatorInterface $validator,
        $class
    ) {
        $this->doctrine       = $doctrine;
        $this->completenessQB = $completenessQB;
        $this->validator = $validator;
        $this->class = $class;
    }

    /**
     * Insert missing completenesses for a given channel
     *
     * @param Channel $channel
     */
    public function createChannelCompletenesses(Channel $channel)
    {
        $this->createCompletenesses(array('channel' => $channel->getId()));
    }

    /**
     * Insert missing completenesses for a given product
     *
     * @param ProductInterface $product
     */
    public function createProductCompletenesses(ProductInterface $product)
    {
        $this->createCompletenesses(array('product' => $product->getId()));
    }

    /**
     * Insert n missing completenesses
     *
     * @param int $limit
     */
    public function createAllCompletenesses($limit = 100)
    {
        $this->createCompletenesses(array(), $limit);
    }

    /**
     * Schedule recalculation of completenesses for a product
     *
     * @param ProductInterface $product
     */
    public function schedule(ProductInterface $product)
    {
        if ($product->getId()) {
            $query = $this->doctrine->getManager()->createQuery(
                "DELETE FROM $this->class c WHERE c.productId = :productId"
            );
            $query->setParameter('productId', $product->getId());
            $query->execute();
        }
    }

    /**
     * Returns an array containing all completeness info and missing attributes for a product
     *
     * @param ProductInterface $product
     * @param array            $channels
     * @param array            $locales
     * @param string           $localeCode
     *
     * @return array
     */
    public function getProductCompleteness(ProductInterface $product, array $channels, array $locales, $localeCode)
    {
        $family = $product->getFamily();

        $getCodes = function ($entities) {
            return array_map(
                function ($entity) {
                    return $entity->getCode();
                },
                $entities
            );
        };
        $channelTemplate = array_fill_keys($getCodes($channels), array('completeness' => null, 'missing' => array()));
        $localeCodes = $getCodes($locales);
        $completenesses = array_fill_keys($localeCodes, $channelTemplate);

        if (!$family) {
            return $completenesses;
        }

        $allCompletenesses = $this->getCompletenessQB($product)->getQuery()->execute();
        foreach ($allCompletenesses as $completeness) {
            $locale = $completeness->getLocale();
            $channel = $completeness->getChannel();
            $completenesses[$locale->getCode()][$channel->getCode()]['completeness'] = $completeness;
        }
        $requirements = $this->doctrine
            ->getRepository(get_class($family))
            ->getFullRequirementsQB($family, $localeCode)
            ->getQuery()
            ->getResult();

        $productValues = $product->getValues();
        foreach ($requirements as $requirement) {
            if ($requirement->isRequired()) {
                $this->addRequirementToCompleteness($completenesses, $requirement, $productValues, $localeCodes);
            }
        }

        return $completenesses;
    }

    /**
     * Adds a requirement to the completenesses
     *
     * @param array                &$completenesses
     * @param AttributeRequirement $requirement
     * @param ArrayCollection      $productValues
     * @param array                $localeCodes
     */
    protected function addRequirementToCompleteness(
        array &$completenesses,
        AttributeRequirement $requirement,
        ArrayCollection $productValues,
        array $localeCodes
    ) {
        $attribute = $requirement->getAttribute();
        $channel = $requirement->getChannel();
        foreach ($localeCodes as $localeCode) {
            $constraint = new ProductValueNotBlank(array('channel' => $channel));
            $valueCode = $attribute->getCode();
            if ($attribute->isTranslatable()) {
                $valueCode .= '_' .$localeCode;
            }
            if ($attribute->isScopable()) {
                $valueCode .= '_' . $channel->getCode();
            }
            $missing = false;
            if (!isset($productValues[$valueCode])) {
                $missing = true;
            } elseif ($this->validator->validateValue($productValues[$valueCode], $constraint)->count()) {
                $missing = true;
            }
            if ($missing) {
                $completenesses[$localeCode][$channel->getCode()]['missing'][] = $attribute;
            }
        }
    }

    /**
     * Returns a query to get the existing completenesses for the product
     *
     * @param ProductInterface $product
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getCompletenessQB(ProductInterface $product)
    {
        return $this->doctrine->getRepository($this->class)
            ->createQueryBuilder('co')
            ->select('co, lo, ch')
            ->innerJoin('co.locale', 'lo')
            ->innerJoin('co.channel', 'ch')
            ->where('co.productId = :productId')
            ->setParameter('productId', $product->getId());
    }

    /**
     * Insert missing completeness according to the criteria
     *
     * @param array   $criteria
     * @param integer $limit
     */
    protected function createCompletenesses(array $criteria, $limit = null)
    {
        $sql = $this->completenessQB->getInsertCompletenessSQL($criteria, $limit);
        $stmt = $this->doctrine->getConnection()->prepare($sql);

        foreach ($criteria as $placeholder => $value) {
            $stmt->bindValue($placeholder, $value);
        }
        $stmt->execute();
    }
}
