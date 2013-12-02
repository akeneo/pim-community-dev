<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Pim\Bundle\CatalogBundle\Doctrine\CompletenessQueryBuilder;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\ProductValueNotBlank;

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
                "DELETE FROM $class c WHERE c.product = :product"
            );
            $query->setParameter('product', $product);
            $query->execute();
        }
    }

    /**
     * Returns an array containing all completeness info and missing attributes for a product
     *
     * @param  ProductInterface $product
     * @param  array            $channels
     * @param  array            $locales
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

        if ($family) {
            $allCompletenesses = $this->doctrine->getRepository($this->class)
                ->createQueryBuilder('co')
                ->select('co, lo, ch')
                ->innerJoin('co.locale', 'lo')
                ->innerJoin('co.channel', 'ch')
                ->where('co.product = :product')
                ->setParameter('product', $product)
                ->getQuery()
                ->execute();
            foreach ($allCompletenesses as $completeness) {
                $locale = $completeness->getLocale();
                $channel = $completeness->getChannel();
                $completenesses[$locale->getCode()][$channel->getCode()]['completeness'] = $completeness;
            }
            $fullFamily = $this->doctrine
                ->getRepository(get_class($family))
                ->createQueryBuilder('f')
                ->select('f, r, a, t')
                ->leftJoin('f.requirements', 'r')
                ->leftJoin('r.attribute', 'a')
                ->leftJoin('a.translations', 't', 'WITH', 't.locale=:localeCode')
                ->where('f.id=:id')
                ->setParameter('id', $family->getId())
                ->setParameter('localeCode', $localeCode)
                ->getQuery()
                ->getOneOrNullResult();
            foreach ($family->getAttributeRequirements() as $requirement) {
                if ($requirement->isRequired()) {
                    $attribute = $requirement->getAttribute();
                    $channel = $requirement->getChannel();
                    foreach ($localeCodes as $localeCode) {
                        $value = $product->getValue($attribute->getCode(), $localeCode, $channel->getCode());
                        $constraint = new ProductValueNotBlank(array('channel' => $channel));

                        if ($this->validator->validateValue($value, $constraint)->count()) {
                            $completenesses[$localeCode][$channel->getCode()]['missing'][] = $attribute;
                        }
                    }
                }
            }
        }

        return $completenesses;
    }

    /**
     * Insert missing completeness according to the criteria
     *
     * @param array   $criteria
     * @param inreger $limit
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
