import React, {useCallback, useState} from 'react';
import {
    Button,
    Field,
    List,
    Locale as LocaleLabel,
    Modal,
    ProgressBar,
    SelectInput,
    SettingsIllustration
} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';


type Props = {
    closeModal: () => void;
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

export const AutoFill = ({closeModal}: Props) => {
    const translate = useTranslate();

    const [step, setStep] = useState<Step>({name: 'catalogs'});
    const [selectedCatalogId, setSelectedCatalogId] = useState<Number | null>(null);
    const [loadingPercentage, setLoadingPercentage] = useState<Number>(0);

    const catalogList = [
        {
            id: 1,
            label : 'amazon.fr'
        },
        {
            id: 2,
            label : 'amazon.de'
        },
        {
            id: 3,
            label : 'amazon.co.uk'
        },
    ];

    const selectCatalog = function (catalogId : number) {
        setSelectedCatalogId(catalogId)
    };

    const applyAutofill = function (catalogId : number) {
        console.log('ouioui');
        setStep({name:'autofill_loading'});
        // startLoading()
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
            { step.name == 'catalogs' &&
                <>
                    <p>Select a catalog which will be used as a model for the target to source
                        attribute association, if a target is not found it will simply be skipped.</p>
                    <ListWrapper>
                        <List>
                            {catalogList.map((catalog) => {
                                return (<List.Row isSelected={catalog.id === selectedCatalogId} key={catalog.id}
                                                  onClick={() => selectCatalog(catalog.id)}>
                                    <List.TitleCell width="auto">{catalog.label}</List.TitleCell>
                                </List.Row>);
                            })}
                        </List>
                    </ListWrapper>
                    <Modal.BottomButtons>
                    <Button level="primary" onClick={() => setStep({name:'scope_and_locale'})}>
                        CONTINUE
                    </Button>
                    </Modal.BottomButtons>
                </>
            }
            { step.name == 'scope_and_locale' &&
                <>
                    <p>Choose the default channel and scope for the attribute that will need one, if some association
                    are not possible, an error will appear upon saving</p>

                    <DropdownField label={translate('akeneo_catalogs.product_mapping.source.parameters.channel.label')}>
                        <SelectInput
                            onChange={() => null}
                            value='ecommerce'
                            clearable={false}
                            invalid={undefined}
                            emptyResultLabel={translate('akeneo_catalogs.common.select.no_matches')}
                            openLabel={translate('akeneo_catalogs.common.select.open')}
                            placeholder={translate('akeneo_catalogs.product_mapping.source.parameters.channel.placeholder')}
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
                            placeholder={translate('akeneo_catalogs.product_mapping.source.parameters.channel.placeholder')}
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
                        <Button level="tertiary" onClick={() => setStep({name:'catalogs'})}>
                            Change the selected Catalog
                        </Button>
                        <Button level="primary" onClick={() => applyAutofill(1)}>
                            Autofill
                        </Button>
                    </Modal.BottomButtons>
                </>
            }
            { step.name == 'autofill_loading' &&
                <>
                    <p>Wait a moment while we are filling your mapping</p>
                    <ProgressBar
                        level="primary"
                        // percent={parseInt(loadingPercentage.toString())}
                        percent={30}
                        progressLabel=""
                        title=""
                    />
                    <Modal.BottomButtons>
                        <Button level="primary" onClick={() => closeModal}>
                            Done
                        </Button>
                    </Modal.BottomButtons>
                </>
            }
        </Modal>
    );
};
