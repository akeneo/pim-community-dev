<?php
declare(strict_types=1);

namespace Pim\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Component\Analytics\DataCollectorInterface;
use Pim\Bundle\AnalyticsBundle\Doctrine\Query;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;

/**
 * Collect data about attributes:
 *  - number of scopable attribute
 *  - number of localizable attribute
 *  - number of localizable and scopable attribute
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeDataCollector implements DataCollectorInterface
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var Query\CountLocalizableAttribute */
    private $countLocalizableAttribute;

    /** @var Query\CountScopableAttribute */
    private $countScopableAttribute;

    /** @var Query\CountScopableAndLocalizableAttribute */
    private $countScopableAndLocalizableAttribute;

    /**
     * @param AttributeRepositoryInterface               $attributeRepository
     * @param Query\CountLocalizableAttribute            $countLocalizableAttribute
     * @param Query\CountScopableAttribute               $countScopableAttribute
     * @param Query\CountScopableAndLocalizableAttribute $countScopableAndLocalizableAttribute
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        Query\CountLocalizableAttribute $countLocalizableAttribute,
        Query\CountScopableAttribute $countScopableAttribute,
        Query\CountScopableAndLocalizableAttribute $countScopableAndLocalizableAttribute
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->countLocalizableAttribute = $countLocalizableAttribute;
        $this->countScopableAttribute = $countScopableAttribute;
        $this->countScopableAndLocalizableAttribute = $countScopableAndLocalizableAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(): array
    {
        $data = [
            'nb_attributes' => $this->attributeRepository->countAll(),
            'nb_scopable_attributes' => ($this->countScopableAttribute)(),
            'nb_localizable_attributes' => ($this->countLocalizableAttribute)(),
            'nb_scopable_localizable_attributes' => ($this->countScopableAndLocalizableAttribute)(),
        ];

        return $data;
    }
}
