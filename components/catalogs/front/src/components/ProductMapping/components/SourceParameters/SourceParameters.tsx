import React, {FC} from 'react';
import {Source} from '../../models/Source';
import {SourceErrors} from '../../models/SourceErrors';
import {SelectLabelLocaleDropdown} from './SelectLabelLocaleDropdown';
import {SelectCurrencyDropdown} from './SelectCurrencyDropdown';
import {SelectChannelCurrencyDropdown} from './SelectChannelCurrenciesDropdown';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {useAttribute} from '../../../../hooks/useAttribute';
import {SourceSectionTitle} from '../SourceSectionTitle';
import {DefaultValue} from './DefaultValue';

const Information = styled.p`
    font-style: italic;
    margin-top: 10px;
`;

type Props = {
    source: Source;
    errors: SourceErrors | null;
    onChange: (value: Source) => void;
    targetType: string;
};

export const SourceParameters: FC<Props> = ({source, errors, onChange, targetType}) => {
    const translate = useTranslate();
    const {data: attribute} = useAttribute(source?.source ?? '');

    const components = [];

    if (undefined !== attribute && null !== source && undefined !== source.parameters) {
        if (undefined !== source.parameters.label_locale) {
            components.push(
                <SelectLabelLocaleDropdown
                    source={source}
                    onChange={onChange}
                    error={errors?.parameters?.label_locale}
                    disabled={attribute.scopable && source.scope === null}
                    key={'select_label_dropdown'}
                />
            );
        }

        if (undefined !== source.parameters.currency && !attribute.scopable) {
            components.push(
                <SelectCurrencyDropdown
                    source={source}
                    onChange={onChange}
                    error={errors?.parameters?.currency}
                    key={'select_currency_dropdown'}
                />
            );
        }

        if (undefined !== source.parameters.currency && attribute.scopable) {
            components.push(
                <SelectChannelCurrencyDropdown
                    source={source}
                    onChange={onChange}
                    error={errors?.parameters?.currency}
                    disabled={attribute.scopable && source.scope === null}
                    key={'select_channel_currency_dropdown'}
                />
            );
        }
    }
    if ('string' === targetType) {
        components.push(
            <DefaultValue
                source={source}
                onChange={onChange}
                error={errors?.default}
                targetType={targetType}
                key={'no_parameters'}
            ></DefaultValue>
        );
    }

    if (components.length === 0) {
        components.push(
            <Information key={'no_parameters'}>
                {translate('akeneo_catalogs.product_mapping.source.parameters.no_parameters_message')}
            </Information>
        );
    }

    return (
        <>
            <SourceSectionTitle order={2}>
                {translate('akeneo_catalogs.product_mapping.source.parameters.title')}
            </SourceSectionTitle>
            {components.map(component => component)}
        </>
    );
};
