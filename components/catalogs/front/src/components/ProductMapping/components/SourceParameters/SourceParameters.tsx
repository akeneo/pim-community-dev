import React, {FC} from 'react';
import {SectionTitle, Tag} from 'akeneo-design-system';
import {Source} from '../../models/Source';
import {SourceErrors} from '../../models/SourceErrors';
import {SelectLabelLocaleDropdown} from './SelectLabelLocaleDropdown';
import {SelectCurrencyDropdown} from './SelectCurrencyDropdown';
import {SelectChannelCurrencyDropdown} from './SelectChannelCurrenciesDropdown';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {useAttribute} from '../../../../hooks/useAttribute';

const Information = styled.p`
    font-style: italic;
    margin-top: 10px;
`;

const SectionSubtitle = () => {
    const translate = useTranslate();
    return (
        <SectionTitle>
            <Tag tint='purple'>2</Tag>
            <SectionTitle.Title level='secondary'>
                {translate('akeneo_catalogs.product_mapping.source.parameters.title')}
            </SectionTitle.Title>
        </SectionTitle>
    );
};

type Props = {
    source: Source | null;
    errors: SourceErrors | null;
    onChange: (value: Source) => void;
};
export const SourceParameters: FC<Props> = ({source, errors, onChange}) => {
    const translate = useTranslate();
    const {data: attribute} = useAttribute(source?.source ?? '');

    if (undefined === attribute || null === source || undefined === source.parameters) {
        return (
            <>
                <SectionSubtitle />
                <Information key={'no_parameters'}>
                    {translate('akeneo_catalogs.product_mapping.source.parameters.no_parameters_message')}
                </Information>
            </>
        );
    }

    return (
        <>
            <SectionSubtitle />
            {undefined !== source.parameters.label_locale && (
                <SelectLabelLocaleDropdown
                    source={source}
                    onChange={onChange}
                    error={errors?.parameters?.label_locale}
                    disabled={attribute.scopable && source.scope === null}
                />
            )}
            {undefined !== source.parameters.currency && !attribute.scopable && (
                <SelectCurrencyDropdown source={source} onChange={onChange} error={errors?.parameters?.currency} />
            )}
            {undefined !== source.parameters.currency && attribute.scopable && (
                <SelectChannelCurrencyDropdown
                    source={source}
                    onChange={onChange}
                    error={errors?.parameters?.currency}
                    disabled={attribute.scopable && source.scope === null}
                />
            )}
        </>
    );
};
