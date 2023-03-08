import React, {FC} from 'react';
import {Field, TextInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Source} from '../../models/Source';

type Props = {
    source: Source;
    onChange: (source: Source) => void;
    error: string | undefined;
    targetType: string;
};
export const DefaultValue: FC<Props> = ({source, onChange, error, targetType}) => {
    const translate = useTranslate();

    if ('string' !== targetType) {
        return null;
    }

    return (
        <>
            <Field label={translate('akeneo_catalogs.product_mapping.source.parameters.default_value.label')}>
                {'string' === targetType && (
                    <TextInput
                        onChange={value => onChange({...source, parameters: {...source.parameters, default: value}})}
                        placeholder={translate(
                            'akeneo_catalogs.product_mapping.source.parameters.default_value.placeholder'
                        )}
                        value={source.parameters?.default ?? ''}
                    />
                )}
            </Field>
        </>
    );
};
