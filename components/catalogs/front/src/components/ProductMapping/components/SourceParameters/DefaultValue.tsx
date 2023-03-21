import React, {FC, useCallback} from 'react';
import {Field, Helper, TextInput} from 'akeneo-design-system';
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

    let element = null;

    switch (targetTypeKey) {
        case 'string':
            element = (
                <TextInput
                    data-testid={'string-default-value'}
                    onChange={value => onChangeMiddleware({...source, default: value})}
                    placeholder={translate(
                        'akeneo_catalogs.product_mapping.source.parameters.default_value.placeholder'
                    )}
                    value={source.default ?? ''}
                />
            );
            break;
    }

    const onChangeMiddleware = useCallback(
        source => {
            if ('string' === target.type && source.default === '') {
                delete source.default;
            }
            onChange(source);
        },

        [onChange, target]
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
