import React, {FC, useCallback} from 'react';
import {BooleanInput, Field, Helper, TextInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Source} from '../../models/Source';
import styled from 'styled-components';
import {Target} from '../../models/Target';

const DefaultField = styled(Field)`
    margin-top: 10px;
`;

type Props = {
    source: Source;
    onChange: (source: Source) => void;
    error: string | undefined;
    target: Target;
};
export const DefaultValue: FC<Props> = ({source, onChange, error, target}) => {
    const translate = useTranslate();

    let targetTypeKey = target.type;

    if (null !== target.format && '' !== target.format) {
        targetTypeKey += `+${target.format}`;
    }

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
                    clearLabel="Clear value"
                    clearable
                    noLabel="No"
                    yesLabel="Yes"
                    value={typeof source.default === 'boolean' ? source.default : null}
                    readOnly={false}
                />
            );
            break;
    }

    const onChangeMiddleware = useCallback(
        source => {
            if (null !== element && source.default === '') {
                delete source.default;
            }
            onChange(source);
        },

        [onChange, target]
    );

    console.log(element);
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
