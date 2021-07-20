<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Connector\FlatTranslator\FlatAttributeValueTranslator;

use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Connector\FlatTranslator\FlatAttributeValueTranslator\ReferenceEntitySimpleLinkValueTranslator;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich\FindRecordsLabelTranslationsInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceEntitySimpleLinkValueTranslatorSpec extends ObjectBehavior
{
    function let(FindRecordsLabelTranslationsInterface $findRecordsLabelTranslations)
    {
        $this->beConstructedWith($findRecordsLabelTranslations);
    }

    function it_is_initializable()
    {
        $this->beAnInstanceOf(ReferenceEntitySimpleLinkValueTranslator::class);
    }

    function it_supports_reference_entity_single_link_attributes()
    {
        $this->supports('akeneo_reference_entity', 'designers')->shouldReturn(true);
        $this->supports('something_else', 'other_attribute')->shouldReturn(false);
    }

    function it_returns_the_labels_of_the_records(FindRecordsLabelTranslationsInterface $findRecordsLabelTranslations)
    {
        $findRecordsLabelTranslations
            ->find('designers', ['dyson', 'starck'], 'fr_FR')
            ->willReturn(['dyson' => 'Dyson', 'starck' => 'Philippe Starck']);

        $this->translate('color', ['reference_data_name' => 'designers'], ['dyson', 'starck'], 'fr_FR')
            ->shouldReturn(['Dyson', 'Philippe Starck']);
    }

    function it_returns_the_record_code_between_brackets_if_the_record_does_have_a_label(FindRecordsLabelTranslationsInterface $findRecordsLabelTranslations)
    {
        $findRecordsLabelTranslations
            ->find('designers', ['michael'], 'fr_FR')
            ->willReturn(['michael' => null]);

        $this->translate('color', ['reference_data_name' => 'designers'], ['michael'], 'fr_FR')
            ->shouldReturn(['[michael]']);
    }

    function it_does_not_translate_if_the_reference_data_name_is_null(FindRecordsLabelTranslationsInterface $findRecordsLabelTranslations)
    {
        $findRecordsLabelTranslations->find()->shouldNotBeCalled();

        $this->shouldThrow(\LogicException::class)
            ->during('translate', ['color', [], ['dyson,starck', 'michael'], 'fr_FR']);
    }
}
