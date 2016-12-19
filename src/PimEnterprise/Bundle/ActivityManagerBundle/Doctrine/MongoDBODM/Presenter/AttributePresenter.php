<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\MongoDBODM\Presenter;

use Pim\Component\Catalog\Repository\AttributeGroupRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Presenter\PresenterInterface;

/**
 * Presents the values coming from MongoDBODM into a comparable data structure.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class AttributePresenter implements PresenterInterface
{
    /** @var AttributeGroupRepositoryInterface */
    protected $attributeGroupRepository;

    /**
     * @param AttributeGroupRepositoryInterface $attributeGroupRepository
     */
    public function __construct(AttributeGroupRepositoryInterface $attributeGroupRepository)
    {
        $this->attributeGroupRepository = $attributeGroupRepository;
    }

    /**
     * Converts the following values:
     *
     *
     * Into:
     *
     * $mandatoryAttributes = [
     *      'marketing' => [
     *          'sku',
     *          'name',
     *      ],
     * ];
     *
     * {@inheritdoc}
     */
    public function present(array $data, array $options = [])
    {
        $attributeCodes = array_keys($data['normalizedData']);
        $result = [];
        foreach ($attributeCodes as $attributeCode) {
            $attributeCode = explode('-', $attributeCode)[0]; //todo fix this
            $attributeGroupCode = $this->attributeGroupRepository
                ->getAttributeGroupsFromAttributeCodes([$attributeCode]);
            if (empty($attributeGroupCode)) {
                continue; //todo find a better way
            }
            $result[$attributeGroupCode[0]['code']][] = $attributeCode;
            $result[$attributeGroupCode[0]['code']] = array_values(array_unique($result[$attributeGroupCode[0]['code']])); //todo hummmm find a better way !!!!
        }

        return $result;
    }
}
