import React, {FC, useCallback} from 'react';
import {Field, Helper, TextInput} from 'akeneo-design-system';
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
    targetType: string;
};
export const DefaultValue: FC<Props> = ({source, onChange, error, targetType}) => {
    const translate = useTranslate();

    const onChangeMiddleware = useCallback(
        source => {
            if ('string' === targetType && source.default === '') {
                delete source.default;
            }
            onChange(source);
        },

        [onChange, targetType]
    );

    if ('string' !== targetType) {
        return null;
    }

    return (
        <>
            <DefaultField label={translate('akeneo_catalogs.product_mapping.source.parameters.default_value.label')}>
                {'string' === targetType && (
                    <TextInput
                        data-testid={'string-default-value'}
                        onChange={value => onChangeMiddleware({...source, default: value})}
                        placeholder={translate(
                            'akeneo_catalogs.product_mapping.source.parameters.default_value.placeholder'
                        )}
                        value={source.default ?? ''}
                    />
                )}
            </DefaultField>
            {undefined !== error && (
                <Helper inline level='error'>
                    {error}
                </Helper>
            )}
        </>
    );
};
