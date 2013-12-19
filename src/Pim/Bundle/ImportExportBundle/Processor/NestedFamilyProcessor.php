<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Pim\Bundle\CatalogBundle\Entity\AttributeRequirement;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\ImportExportBundle\Transformer\ORMTransformer;
use Pim\Bundle\ImportExportBundle\Validator\Import\ImportValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
* Nested processor for families
*
* @author Antoine Guigan <antoine@akeneo.com>
* @copyright 2013 Akeneo SAS (http://www.akeneo.com)
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
*/
class NestedFamilyProcessor extends TransformerProcessor
{
    /**
* @var string
*/
    protected $requirementClass;

    /**
* Constructor
*
* @param ImportValidatorInterface $validator
* @param TranslatorInterface $translator
* @param ORMTransformer $transformer
* @param string $class
* @param string $requirementClass
*/
    public function __construct(
        ImportValidatorInterface $validator,
        TranslatorInterface $translator,
        ORMTransformer $transformer,
        $class,
        $requirementClass
    ) {
        parent::__construct($validator, $translator, $transformer, $class);
        $this->requirementClass = $requirementClass;
    }

    /**
* {@inheritdoc}
*/
    protected function transform($item)
    {
        if (isset($item['requirements'])) {
            $requirementsData = $item['requirements'];
            unset($item['requirements']);
        }

        $family = parent::transform($item);

        if (!count($this->transformer->getErrors()) && isset($requirementsData)) {
            $requirements = array();
            foreach ($requirementsData as $channelCode => $attributeCodes) {
                $requirements = array_merge(
                    $requirements,
                    $this->getRequirements($family, $channelCode, $attributeCodes)
                );
                if (count($this->transformer->getErrors())) {
                    break;
                }
            }
            $family->setAttributeRequirements($requirements);
        }

        return $family;
    }

    /**
* Returns the requirements for a channel
*
* @param Family $family
* @param string $channelCode
* @param array $attributeCodes
* @return AttributeRequirement[]
*/
    protected function getRequirements(Family $family, $channelCode, $attributeCodes)
    {
        $requirements = array();
        foreach ($attributeCodes as $attributeCode) {
            $requirement = $this->transformer->transform(
                $this->requirementClass,
                array(
                    'attribute' => $attributeCode,
                    'channel' => $channelCode,
                    'required' => true
                )
            );
            if (count($this->transformer->getErrors())) {
                break;
            }

            $requirements[] = $requirement;
        }

        return $requirements;
    }

    protected function getTransformedColumnsInfo()
    {
        return array();
    }
}
