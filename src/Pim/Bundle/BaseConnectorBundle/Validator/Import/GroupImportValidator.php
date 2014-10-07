<?php

namespace Pim\Bundle\BaseConnectorBundle\Validator\Import;

use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\Repository\GroupRepository;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Validates an imported group
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupImportValidator extends ImportValidator
{
    /**
     * @var GroupRepository $groupRepository
     */
    protected $groupRepository;

    /**
     * Constructor
     *
     * @param ValidatorInterface $validator
     * @param GroupRepository    $groupRepository
     */
    public function __construct(
        ValidatorInterface $validator,
        GroupRepository $groupRepository
    ) {
        parent::__construct($validator);

        $this->groupRepository = $groupRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($entity, array $columnsInfo, array $data, array $errors = [])
    {
        $errors = parent::validate($entity, $columnsInfo, $data, $errors);

        if ($entity->getType()->isVariant() && null !== $entity->getId()) {
            $attributeCodes = $this->getAttributesFromDB($entity);
            $csvAttributes = explode(',', $data['attributes']);

            $areEquals = $this->areArrayEquals($this->getAttributeCode($attributeCodes), $csvAttributes);

            if (!$areEquals) {
                $errors['attributes'] = [['attributes' => 'Attributes cannot be changed']];
            }
        }

        return $errors;
    }

    /**
     * Return all the variant group attribute as an array
     *
     * @param Group $variantGroup
     *
     * @return array
     */
    protected function getAttributesFromDB(Group $variantGroup)
    {
        return $this->groupRepository
            ->createQueryBuilder('g')
            ->select('a.code')
            ->setParameter('id', $variantGroup->getId())
            ->join('g.attributes', 'a')
            ->where('g.id = :id')
            ->getQuery()
            ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    }

    /**
     * Return attribute code for each attribute sorted by lexical order
     *
     * @param array $attributes
     *
     * @return array
     */
    protected function getAttributeCode($attributes)
    {
        $attributesCode = [];

        foreach ($attributes as $attribute) {
            $attributesCode[] = $attribute['code'];
        }

        return $attributesCode;
    }

    /**
     * Removes spaces, sort the arrays and checks if they are equals
     *
     * @param array $array1
     * @param array $array2
     *
     * @return bool Returns true if the two arrays are equals
     */
    private function areArrayEquals($array1, $array2)
    {
        asort($array1);
        asort($array2);

        $array1 = array_values(array_filter($array1));
        $array2 = array_values(array_filter($array2));

        return ($array1 == $array2);
    }
}
