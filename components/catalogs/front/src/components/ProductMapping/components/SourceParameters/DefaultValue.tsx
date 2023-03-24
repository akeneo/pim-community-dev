import React, {FC, useCallback} from 'react';
import {BooleanInput, Field, Helper, NumberInput, TextInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Source} from '../../models/Source';
import styled from 'styled-components';

const DefaultField = styled(Field)`
    margin-top: 10px;
`;

type Props = {
    source: Source;
    onChange: (source: Source) => void;
    error: string | undefined;
    targetTypeKey: string;
};
export const DefaultValue: FC<Props> = ({source, onChange, error, targetTypeKey}) => {
    const translate = useTranslate();

    let element: JSX.Element | null = null;

    switch (targetTypeKey) {
        case 'string':
            element = (
                <TextInput
                    data-testid={'string-default-value'}
                    onChange={value => onChangeMiddleware({...source, default: value})}
                    placeholder={translate(
                        'akeneo_catalogs.product_mapping.source.parameters.default_value.placeholder'
                    )}
                    value={typeof source.default === 'string' ? source.default : ''}
                />
            );
            break;
        case 'boolean':
            element = (
                <BooleanInput
                    data-testid={'boolean-default-value'}
                    onChange={value => onChangeMiddleware({...source, default: value})}
                    clearLabel='Clear value'
                    clearable
                    noLabel='No'
                    yesLabel='Yes'
                    value={typeof source.default === 'boolean' ? source.default : null}
                    readOnly={false}
                />
            );
            break;
        case 'number':
            element = (
                <NumberInput
                    data-testid={'number-default-value'}
                    onChange={value => onChangeMiddleware({...source, default: value})}
                    placeholder={translate(
                        'akeneo_catalogs.product_mapping.source.parameters.default_value.placeholder'
                    )}
                    value={typeof source.default === 'number' ? source.default.toString() : ''}
                />
            );
            break;
    }

    const onChangeMiddleware = useCallback(
        source => {
            if (targetTypeKey === 'string' && source.default === '') {
                delete source.default;
            }
            if (targetTypeKey === 'boolean' && source.default === null) {
                delete source.default;
            }
            if (targetTypeKey === 'number') {
                if (source.default === '') {
                    delete source.default;
                } else {
                    source.default = parseFloat(source.default);
                }
            }
            onChange(source);
        },
        [onChange, targetTypeKey]
    );

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
