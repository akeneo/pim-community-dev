import React from 'react';
import styled from 'styled-components';
import {Field, SectionTitle, TextInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Supplier} from "../../models";

type Props = {
    supplier: Supplier;
    setLabel: (value: string) => void;
};

const Configuration = ({supplier, setLabel}: Props) => {
    const translate = useTranslate();

    return (
        <TabContainer>
            <SectionTitle>
                <SectionTitle.Title>{translate('pim_common.general_properties')}</SectionTitle.Title>
            </SectionTitle>
            <Field label={translate('onboarder.supplier.supplier_edit.configuration_form.code')}>
                <TextInput readOnly value={supplier.code} />
            </Field>
            <Field label={translate('onboarder.supplier.supplier_edit.configuration_form.label')}>
                <TextInput onChange={setLabel} value={supplier.label} />
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
