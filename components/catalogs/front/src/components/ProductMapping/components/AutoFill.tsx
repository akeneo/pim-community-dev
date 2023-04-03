import React, {useState} from 'react';
import {
    Button,
    Field,
    List,
    Locale as LocaleLabel,
    Modal,
    ProgressBar,
    SectionTitle,
    SelectInput,
    SettingsIllustration,
} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {Source} from '../models/Source';
import {ProductMapping as ProductMappingType} from '../models/ProductMapping';

type Props = {
    closeModal: () => void;
    productMapping: ProductMappingType;
    onChange: (value: Source) => void;
};

export type Step = {
    name: 'catalogs' | 'scope_and_locale' | 'autofill_loading';
};

const ListWrapper = styled.div`
    max-height: 300px;
    overflow-y: scroll;
`;

const DropdownField = styled(Field)`
    margin-top: 10px;
`;

export const AutoFill = ({closeModal, productMapping, onChange}: Props) => {
    const translate = useTranslate();

    const [step, setStep] = useState<Step>({name: 'catalogs'});
    const [selectedCatalogId, setSelectedCatalogId] = useState<Number | null>(null);
    const [loadingPercentage, setLoadingPercentage] = useState<Number>(0);

    const catalogList = [
        {
            id: 1,
            label: 'amazon.fr',
        },
        {
            id: 2,
            label: 'amazon.de',
        },
        {
            id: 3,
            label: 'amazon.co.uk',
        },
    ];

    const selectCatalog = function (catalogId: number) {
        setSelectedCatalogId(catalogId);
    };

    const applyAutofill = function (catalogId: number) {
        console.log('ouioui');
        setStep({name: 'autofill_loading'});
        // startLoading()
        callAI();
    };

    const onAutoFillAIChange = function () {
        // onChange({
        //     ...productMapping,
        //     [selectedTarget.code]: source,
        // });
    };

    const callAI = async function () {
        const API_KEY = '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';

        const response = await fetch('https://api.openai.com/v1/chat/completions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Authorization: 'Bearer ' + API_KEY,
            },
            body: JSON.stringify({
                model: 'gpt-3.5-turbo',
                messages: [
                    {
                        role: 'user',
                        content:
                            'Complete the two-column spreadsheet of word association by meaning, between the List A and the List B, the first column of the spreadsheet must include all the words from the List A:\n' +
                            '\n' +
                            'List A : \n' +
                            'Name\n' +
                            'Collection\n' +
                            'Description\n' +
                            'Brand\n' +
                            'Response time (ms)\n' +
                            'Variant Name\n' +
                            'Variant description\n' +
                            'EAN\n' +
                            'SKU\n' +
                            'Supplier\n' +
                            'ERP name\n' +
                            'Power requirements\n' +
                            'Maximum print size\n' +
                            'Sensor type\n' +
                            'Total megapixels\n' +
                            'Optical zoom\n' +
                            'Camera type\n' +
                            'Total Harmonic Distortion (THD)\n' +
                            'Signal-to-Noise Ratio (SNR)\n' +
                            'Headphone connectivity]\n' +
                            '\n' +
                            'List B :\n' +
                            'name\n' +
                            'description\n' +
                            'power_requirements\n' +
                            'maximum_print_size\n' +
                            'sensor_type\n' +
                            'total_megapixels\n' +
                            'camera_type\n' +
                            'headphone_connectivity\n' +
                            'maximum_video_resolution\n' +
                            'multifunctional_functions\n' +
                            'camera_brand\n' +
                            'camera_model_name\n' +
                            'short_description\n' +
                            'max_image_resolution\n' +
                            'image_resolutions\n' +
                            'supported_aspect_ratios\n' +
                            'supported_image_format\n' +
                            'lens_mount_interface\n' +
                            'focus\n' +
                            'focus_adjustement\n' +
                            'auto_focus_modes\n' +
                            'iso_sensitivity\n' +
                            'light_exposure_modes\n' +
                            'light_exposure_corrections\n' +
                            'light_metering\n' +
                            'container_material\n' +
                            'tshirt_materials\n' +
                            'main_color\n' +
                            'secondary_color\n' +
                            'clothing_size\n' +
                            'tshirt_style\n' +
                            'erp_name\n' +
                            'meta_title\n' +
                            'meta_description\n' +
                            'keywords\n' +
                            'variation_name\n' +
                            'variation_description\n' +
                            'collection\n' +
                            'composition\n' +
                            'wash_temperature\n' +
                            'care_instructions\n' +
                            'color\n' +
                            'size\n' +
                            'eu_shoes_size\n' +
                            'sole_composition\n' +
                            'top_composition\n' +
                            'brand\n' +
                            'ean\n' +
                            'supplier\n' +
                            'material\n' +
                            'SKU\n' +
                            '\n' +
                            'Here is the format we want : \n' +
                            '\n' +
                            '-------##------\n' +
                            'Name##name',
                    },
                ],
                temperature: 0,
                max_tokens: 454,
            }),
        });

        if (!response.ok) {
            // console.error('HTTP ERROR: ' + response.status + '\n' + response.statusText);
        } else {
            const data = await response.json();
            const mappingAIRaw = data.choices[0].message.content;
            let mappingAI = mappingAIRaw.split('\n');

            mappingAI = mappingAI.map((mappedRaw: string) => mappedRaw.split('##'));

            console.log(mappingAI);
        }
    };

    // const startLoading = function () {
    //     console.log(loadingPercentage);
    //     if (loadingPercentage > 100) {
    //         return;
    //     }
    //     setTimeout(() => {
    //         const percentage = parseInt(loadingPercentage.toString()) + 1;
    //         console.log(percentage);
    //         setLoadingPercentage(percentage);
    //         startLoading();
    //     }, 50);
    // }
    //
    // const handleClick = useCallback(
    //     (targetCode, source) => {
    //         setSelectedTarget(targetCode);
    //         setSelectedTargetLabel(productMappingSchema?.properties[targetCode]?.title ?? targetCode);
    //         setSelectedSource(source);
    //     },
    //     [productMappingSchema]
    // );

    return (
        <Modal onClose={closeModal} closeTitle={translate('pim_common.close')} illustration={<SettingsIllustration />}>
            <Modal.Title>Autofill my mapping</Modal.Title>
            {step.name == 'catalogs' && (
                <>
                    <SectionTitle>
                        <SectionTitle.Title level='secondary'>Based on another catalog</SectionTitle.Title>
                    </SectionTitle>
                    <br />
                    <p>
                        Select a catalog which will be used as a model for the target to source attribute association,
                        if a target is not found it will simply be skipped.
                    </p>
                    <ListWrapper>
                        <List>
                            {catalogList.map(catalog => {
                                return (
                                    <List.Row
                                        isSelected={catalog.id === selectedCatalogId}
                                        key={catalog.id}
                                        onClick={() => selectCatalog(catalog.id)}
                                    >
                                        <List.TitleCell width='auto'>{catalog.label}</List.TitleCell>
                                    </List.Row>
                                );
                            })}
                        </List>
                    </ListWrapper>
                    <Modal.BottomButtons>
                        <Button level='primary' onClick={() => setStep({name: 'scope_and_locale'})}>
                            Continue with catalog selection
                        </Button>
                    </Modal.BottomButtons>
                    <br />
                    <SectionTitle>
                        <SectionTitle.Title level='secondary'>Based on AI</SectionTitle.Title>
                    </SectionTitle>
                    <br />
                    <p>AI will match target label with pim attributes ! It&apos;s amazing.</p>
                    <Modal.BottomButtons>
                        <Button level='primary' onClick={() => setStep({name: 'scope_and_locale'})}>
                            Continue with AI
                        </Button>
                    </Modal.BottomButtons>
                </>
            )}
            {step.name == 'scope_and_locale' && (
                <>
                    <p>
                        Choose the default channel and scope for the attribute that will need one, if some association
                        are not possible, an error will appear upon saving
                    </p>

                    <DropdownField label={translate('akeneo_catalogs.product_mapping.source.parameters.channel.label')}>
                        <SelectInput
                            onChange={() => null}
                            value='ecommerce'
                            clearable={false}
                            invalid={undefined}
                            emptyResultLabel={translate('akeneo_catalogs.common.select.no_matches')}
                            openLabel={translate('akeneo_catalogs.common.select.open')}
                            placeholder={translate(
                                'akeneo_catalogs.product_mapping.source.parameters.channel.placeholder'
                            )}
                            data-testid='source-parameter-channel-dropdown'
                        >
                            <SelectInput.Option key='ecommerce' title='Ecommerce' value='ecommerce'>
                                Ecommerce
                            </SelectInput.Option>
                        </SelectInput>
                    </DropdownField>
                    <DropdownField label={translate('akeneo_catalogs.product_mapping.source.parameters.locale.label')}>
                        <SelectInput
                            onChange={() => null}
                            value='en-US'
                            clearable={false}
                            invalid={undefined}
                            emptyResultLabel={translate('akeneo_catalogs.common.select.no_matches')}
                            openLabel={translate('akeneo_catalogs.common.select.open')}
                            placeholder={translate(
                                'akeneo_catalogs.product_mapping.source.parameters.channel.placeholder'
                            )}
                            data-testid='source-parameter-channel-dropdown'
                        >
                            <SelectInput.Option key='en-US' title='English (United States)' value='en-US'>
                                <LocaleLabel code='en-US' languageLabel='English (United States)' />
                            </SelectInput.Option>
                            <SelectInput.Option key='fr-Fr' title='French (France)' value='fr-Fr'>
                                <LocaleLabel code='fr-Fr' languageLabel='French (France)' />
                            </SelectInput.Option>
                        </SelectInput>
                    </DropdownField>
                    <Modal.BottomButtons>
                        <Button level='tertiary' onClick={() => setStep({name: 'catalogs'})}>
                            Change the autofilling mode
                        </Button>
                        <Button level='primary' onClick={() => applyAutofill(1)}>
                            Autofill
                        </Button>
                    </Modal.BottomButtons>
                </>
            )}
            {step.name == 'autofill_loading' && (
                <>
                    <p>Wait a moment while we are filling your mapping</p>
                    <ProgressBar
                        level='primary'
                        // percent={parseInt(loadingPercentage.toString())}
                        percent={30}
                        progressLabel=''
                        title=''
                    />
                    <Modal.BottomButtons>
                        <Button level='primary' onClick={() => closeModal}>
                            Done
                        </Button>
                    </Modal.BottomButtons>
                </>
            )}
        </Modal>
    );
};
