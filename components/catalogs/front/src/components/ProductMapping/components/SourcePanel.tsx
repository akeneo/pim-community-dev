import React, {FC, useCallback} from 'react';
import {SectionTitle, Tag} from 'akeneo-design-system';
import {SourcePlaceholder} from './SourcePlaceholder';
import {SelectAttributeDropdown} from './SelectAttributeDropdown';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Source} from '../models/Source';
import {Attribute} from '../../../models/Attribute';
import {SelectChannelDropdown} from './SelectChannelDropdown';
import {useAttribute} from '../../../hooks/useAttribute';
import {SourceErrors} from '../models/SourceErrors';
import {SelectLocaleDropdown} from './SelectLocaleDropdown';
import {SelectChannelLocaleDropdown} from './SelectChannelLocaleDropdown';
import {SourceUuidPlaceholder} from './SourceUuidPlaceholder';
import {SelectLabelLocaleDropdown} from './SelectLabelLocaleDropdown';

type Props = {
    target: string | null;
    source: Source | null;
    targetLabel: string | null;
    onChange: (value: Source) => void;
    errors: SourceErrors | null;
};

export const SourcePanel: FC<Props> = ({target, source, targetLabel, onChange, errors}) => {
    const translate = useTranslate();
    const {data: attribute} = useAttribute('uuid' !== target && source?.source ? source.source : '');
    const handleSourceSelection = useCallback(
        (value: Attribute) => {
            onChange(initSource(value));
        },
        [onChange]
    );

    const initSource = function (attribute: Attribute): Source {
        let source: Source = {
            source: attribute.code,
            locale: null,
            scope: null,
        };
        switch (attribute.type) {
            case 'pim_catalog_simpleselect':
                source = {...source, parameters: {...source.parameters, label_locale: null}};
                break;
        }

        return source;
    };

    const onChangeMiddleware = useCallback(
        source => {
            if (
                attribute?.type === 'pim_catalog_simpleselect' &&
                (source.parameters.label_locale === undefined || source.parameters.label_locale === null)
            ) {
                source = {...source, parameters: {...source.parameters, label_locale: source.locale ?? null}};
            }
            onChange(source);
        },
        [onChange, attribute]
    );

    const shouldDisplayChannel = source !== null && attribute?.scopable;
    const shouldDisplayLocale = source !== null && attribute?.localizable && !attribute?.scopable;
    const shouldDisplayChannelLocale = source !== null && attribute?.localizable && attribute?.scopable;
    const shouldDisplayTranslationValue = source !== null && attribute?.type === 'pim_catalog_simpleselect';

    return (
        <>
            {null === target && <SourcePlaceholder />}
            {'uuid' === target && <SourceUuidPlaceholder targetLabel={targetLabel} />}
            {target && 'uuid' !== target && (
                <>
                    <SectionTitle>
                        <SectionTitle.Title>{targetLabel}</SectionTitle.Title>
                    </SectionTitle>
                    <SectionTitle>
                        <Tag tint='purple'>1</Tag>
                        <SectionTitle.Title level='secondary'>
                            {translate('akeneo_catalogs.product_mapping.source.title')}
                        </SectionTitle.Title>
                    </SectionTitle>
                    <SelectAttributeDropdown
                        code={source?.source ?? ''}
                        onChange={handleSourceSelection}
                        error={errors?.source}
                    ></SelectAttributeDropdown>
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
                </>
            )}
        </>
    );
};
