import React, {useState} from 'react';
import {Button, List, Modal, SelectInput, SettingsIllustration} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {Channel} from '../../../models/Channel';


type Props = {
    closeModal: () => void;
};

export type Step = {
    name: 'catalogs' | 'scope_and_locale';
};

const ListWrapper = styled.div`
    max-height: 300px;
    overflow-y: scroll;
`;

export const AutoFill = ({closeModal}: Props) => {
    const translate = useTranslate();

    const [step, setStep] = useState<Step>({name: 'catalogs'});
    const [selectedCatalogId, setSelectedCatalogId] = useState<Number | null>(null);

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

    const channels = [
        {
            label : 'Ecommerce',
            code : 'ecom',
        },
        {
            label : 'Print',
            code : 'print',
        }
    ];

    const selectCatalog = function (catalogId : number) {
        console.log(catalogId);
        setSelectedCatalogId(catalogId)
    };

    const applyAutofill = function (catalogId : number) {
        closeModal();
    };

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

        {/*<SelectInput*/}
        {/*    value={source.scope}*/}
        {/*    onChange={newChannel => onChange({...source, scope: newChannel, locale: null})}*/}
        {/*    onNextPage={fetchNextPage}*/}
        {/*    clearable={false}*/}
        {/*    invalid={error !== undefined}*/}
        {/*    emptyResultLabel={translate('akeneo_catalogs.common.select.no_matches')}*/}
        {/*    openLabel={translate('akeneo_catalogs.common.select.open')}*/}
        {/*    placeholder={translate('akeneo_catalogs.product_mapping.source.parameters.channel.placeholder')}*/}
        {/*    data-testid='source-parameter-channel-dropdown'*/}
        {/*>*/}
        {/*    {channels?.map(channel => (*/}
        {/*        <SelectInput.Option key={channel.code} title={channel.label} value={channel.code}>*/}
        {/*            {channel.label}*/}
        {/*        </SelectInput.Option>*/}
        {/*    ))}*/}
        {/*</SelectInput>*/}
                    <Modal.BottomButtons>
                        <Button level="primary" onClick={() => applyAutofill}>
                            Autofill my mapping
                        </Button>
                    </Modal.BottomButtons>
                </>
            }
        </Modal>
    );
};
