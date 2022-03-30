import React from 'react';
import styled from 'styled-components';
import {Field, SectionTitle, TextInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

type Props = {
    code: string;
    label: string;
    setLabel: (value: string) => void;
};

const Configuration = ({code, label, setLabel}: Props) => {
    const translate = useTranslate();

    return (
        <TabContainer>
            <SectionTitle>
                <SectionTitle.Title>{translate('pim_common.general_properties')}</SectionTitle.Title>
            </SectionTitle>
            <Field label={translate('onboarder.supplier.supplier_edit.configuration_form.code')}>
                <TextInput readOnly value={code} />
            </Field>
            <Field label={translate('onboarder.supplier.supplier_edit.configuration_form.label')}>
                <TextInput onChange={setLabel} value={label} />
            </Field>
        </TabContainer>
    );
};

const TabContainer = styled.div`
    & > * {
        margin: 0 10px 20px 0;
    }
`;

export {Configuration};
