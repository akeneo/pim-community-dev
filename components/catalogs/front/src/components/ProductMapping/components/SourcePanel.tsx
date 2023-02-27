import React, {FC, useCallback} from 'react';
import {SectionTitle, Tag} from 'akeneo-design-system';
import {SourcePlaceholder} from './SourcePlaceholder';
import {SelectSourceAttributeDropdown} from './SelectSourceAttributeDropdown';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Source} from '../models/Source';
import {Attribute} from '../../../models/Attribute';
import {SelectChannelDropdown} from './SelectChannelDropdown';
import {useAttribute} from '../../../hooks/useAttribute';
import {SourceErrors} from '../models/SourceErrors';
import {SelectLocaleDropdown} from './SelectLocaleDropdown';
import {SelectChannelLocaleDropdown} from './SelectChannelLocaleDropdown';
import {SourceUuidPlaceholder} from './SourceUuidPlaceholder';
import styled from 'styled-components';
import {Target} from '../models/Target';
import {SelectLabelLocaleDropdown} from './SelectLabelLocaleDropdown';
import {RequirementsCollapse} from './RequirementsCollapse';
import {SelectCurrencyDropdown} from './SelectCurrencyDropdown';
import {SelectChannelCurrencyDropdown} from './SelectChannelCurrenciesDropdown';
import {SelectMeasurementUnitDropdown} from './SelectMeasurementUnitDropdown';

type Props = {
    target: Target | null;
    source: Source | null;
    onChange: (value: Source) => void;
    errors: SourceErrors | null;
};

const Information = styled.p`
    font-style: italic;
    margin-top: 10px;
`;

export const SourcePanel: FC<Props> = ({target, source, onChange, errors}) => {
    const translate = useTranslate();
    const {data: attribute} = useAttribute('uuid' !== target?.code && source?.source ? source.source : '');

    const attributeType: string = attribute?.type ?? '';
    const shouldDisplayTranslationValue =
        source !== null &&
        (attributeType === 'pim_catalog_simpleselect' || attributeType === 'pim_catalog_multiselect');
    const shouldDisplayCurrency =
        source !== null && !attribute?.scopable && attribute?.type === 'pim_catalog_price_collection';
    const shouldDisplayChannelCurrency =
        source !== null && attribute?.scopable && attribute?.type === 'pim_catalog_price_collection';
    const shouldDisplayChannel = source !== null && attribute?.scopable;
    const shouldDisplayLocale = source !== null && attribute?.localizable && !attribute?.scopable;
    const shouldDisplayChannelLocale = source !== null && attribute?.localizable && attribute?.scopable;
    const shouldDisplayMeasurementUnits = source !== null && attribute?.type === 'pim_catalog_metric';
    const shouldDisplayNoParametersMessage = !(
        shouldDisplayLocale ||
        shouldDisplayChannel ||
        shouldDisplayChannelLocale ||
        shouldDisplayCurrency ||
        shouldDisplayChannelCurrency ||
        shouldDisplayTranslationValue ||
        shouldDisplayMeasurementUnits
    );

    const initSource = useCallback(
        (attribute: Attribute): Source => {
            let source: Source = {
                source: attribute.code,
                locale: null,
                scope: null,
            };
            switch (attribute.type) {
                case 'pim_catalog_simpleselect':
                    source = {...source, parameters: {...source.parameters, label_locale: null}};
                    break;
                case 'pim_catalog_metric':
                    source = {...source, parameters: {...source.parameters, unit: attribute.default_measurement_unit}};
                    break;
                case 'pim_catalog_price_collection':
                    source = {...source, parameters: {...source.parameters, currency: null}};
                    break;
            }
            return source;
        },
        [shouldDisplayTranslationValue]
    );

    const handleSourceSelection = useCallback(
        (value: Attribute) => {
            onChange(initSource(value));
        },
        [onChange, initSource]
    );

    const onChangeMiddleware = useCallback(
        source => {
            if (
                shouldDisplayTranslationValue &&
                (source.parameters?.label_locale === undefined || source.parameters?.label_locale === null)
            ) {
                source = {...source, parameters: {...source.parameters, label_locale: source.locale ?? null}};
            }

            if (attribute?.type === 'pim_catalog_price_collection' && !(source.parameters.currency ?? false)) {
                source = {...source, parameters: {...source.parameters, currency: source.currency ?? null}};
            }
            onChange(source);
        },

        [onChange, shouldDisplayTranslationValue]
    );

    if (null === target) {
        return <SourcePlaceholder />;
    }

    if ('uuid' === target.code) {
        return <SourceUuidPlaceholder targetLabel={target.label} />;
    }

    return (
        <>
            <SectionTitle>
                <SectionTitle.Title>{target.label}</SectionTitle.Title>
            </SectionTitle>
            <RequirementsCollapse target={target} />
            <SectionTitle>
                <Tag tint='purple'>1</Tag>
                <SectionTitle.Title level='secondary'>
                    {translate('akeneo_catalogs.product_mapping.source.title')}
                </SectionTitle.Title>
            </SectionTitle>
            <SelectSourceAttributeDropdown
                selectedCode={source?.source ?? ''}
                target={target}
                onChange={handleSourceSelection}
                error={errors?.source}
            />
            <SectionTitle>
                <Tag tint='purple'>2</Tag>
                <SectionTitle.Title level='secondary'>
                    {translate('akeneo_catalogs.product_mapping.source.parameters.title')}
                </SectionTitle.Title>
            </SectionTitle>
            {shouldDisplayChannel && (
                <SelectChannelDropdown source={source} onChange={onChangeMiddleware} error={errors?.scope} />
            )}
            {shouldDisplayLocale && (
                <SelectLocaleDropdown source={source} onChange={onChangeMiddleware} error={errors?.locale} />
            )}
            {shouldDisplayChannelLocale && (
                <SelectChannelLocaleDropdown
                    source={source}
                    onChange={onChangeMiddleware}
                    error={errors?.locale}
                    disabled={attribute && source ? attribute.scopable && source.scope === null : false}
                />
            )}
            {shouldDisplayTranslationValue && (
                <SelectLabelLocaleDropdown
                    source={source}
                    onChange={onChange}
                    error={errors?.parameters?.label_locale}
                    disabled={attribute && source ? attribute.scopable && source.scope === null : false}
                />
            )}
            {shouldDisplayCurrency && (
                <SelectCurrencyDropdown
                    source={source}
                    onChange={onChangeMiddleware}
                    error={errors?.parameters?.currency}
                />
            )}
            {shouldDisplayChannelCurrency && (
                <SelectChannelCurrencyDropdown
                    source={source}
                    onChange={onChangeMiddleware}
                    error={errors?.parameters?.currency}
                    disabled={attribute && source ? attribute.scopable && source.scope === null : false}
                />
            )}
            {shouldDisplayMeasurementUnits && (
                <SelectMeasurementUnitDropdown
                    source={source}
                    onChange={onChange}
                    error={errors?.parameters?.unit}
                    measurementFamily={attribute?.measurement_family ?? null}
                />
            )}
            {shouldDisplayNoParametersMessage && (
                <Information key={'no_parameters'}>
                    {translate('akeneo_catalogs.product_mapping.source.parameters.no_parameters_message')}
                </Information>
            )}
        </>
    );
};
