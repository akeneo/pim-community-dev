import React from 'react';
import styled from 'styled-components';
import {Field, Helper, SectionTitle, TextInput} from 'akeneo-design-system';
import {getErrorsForPath, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {Supplier, LABEL_AND_CODE_MAX_LENGTH} from '../../models';

type Props = {
    supplier: Supplier;
    setLabel: (value: string) => void;
    validationErrors: ValidationError[];
};

const Configuration = ({supplier, setLabel, validationErrors}: Props) => {
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
                <TextInput onChange={setLabel} value={supplier.label} maxLength={LABEL_AND_CODE_MAX_LENGTH} />
                {getErrorsForPath(validationErrors, 'label').map((error, index) => (
                  <Helper key={index} level="error">
                    {translate(error.message)}
                  </Helper>
                ))}
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
