import React, {FC, useCallback} from 'react';
import {BooleanInput, Field, Helper, NumberInput, TextInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';

const DefaultField = styled(Field)`
    margin-top: 10px;
`;

const sanitizeDefaultValue = (value: string | boolean | null, targetTypeKey: string): DefaultValue => {
    if (targetTypeKey === 'string' && value === '') {
        return undefined;
    }
    if (targetTypeKey === 'boolean' && value === null) {
        return undefined;
    }
    if (targetTypeKey === 'number' && value === '') {
        return undefined;
    }

    if (targetTypeKey === 'number' && typeof value === 'string' && value !== '') {
        return parseFloat(value);
    }

    return value;
};

type DefaultValue = string | boolean | number | null | undefined;
type Props = {
    value: DefaultValue;
    onChange: (newValue: DefaultValue) => void;
    error: string | undefined;
    targetTypeKey: string;
};
export const DefaultValue: FC<Props> = ({value, onChange, error, targetTypeKey}) => {
    const translate = useTranslate();

    const onChangeMiddleware = useCallback(
        newValue => onChange(sanitizeDefaultValue(newValue, targetTypeKey)),
        [onChange, targetTypeKey]
    );

    let element: JSX.Element | null = null;

    switch (targetTypeKey) {
        case 'string':
            element = (
                <TextInput
                    data-testid={'string-default-value'}
                    onChange={onChangeMiddleware}
                    placeholder={translate(
                        'akeneo_catalogs.product_mapping.source.parameters.default_value.placeholder'
                    )}
                    value={typeof value === 'string' ? value : ''}
                />
            );
            break;
        case 'boolean':
            element = (
                <BooleanInput
                    data-testid={'boolean-default-value'}
                    onChange={onChangeMiddleware}
                    clearLabel='Clear value'
                    clearable
                    noLabel='No'
                    yesLabel='Yes'
                    value={typeof value === 'boolean' ? value : null}
                    readOnly={false}
                />
            );
            break;
        case 'number':
            element = (
                <NumberInput
                    data-testid={'number-default-value'}
                    onChange={onChangeMiddleware}
                    placeholder={translate(
                        'akeneo_catalogs.product_mapping.source.parameters.default_value.placeholder'
                    )}
                    value={typeof value === 'number' ? value.toString() : ''}
                />
            );
            break;
    }

    if (null === element) {
        return null;
    }

    return (
        <>
            <DefaultField label={translate('akeneo_catalogs.product_mapping.source.parameters.default_value.label')}>
                {element}
            </DefaultField>
            {undefined !== error && (
                <Helper inline level='error'>
                    {error}
                </Helper>
            )}
        </>
    );
};
