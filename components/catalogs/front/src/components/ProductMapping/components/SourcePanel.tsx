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

type Props = {
    target: string | null;
    source: Source | null;
    targetLabel: string | null;
    onChange: (value: Source) => void;
    errors: SourceErrors | null;
};

export const SourcePanel: FC<Props> = ({target, source, targetLabel, onChange, errors}) => {
    const translate = useTranslate();
    const {data: attribute} = useAttribute(source?.source ?? '');
    const handleSourceSelection = useCallback(
        (value: Attribute) => {
            onChange({
                source: value.code,
                locale: null,
                scope: null,
            });
        },
        [onChange]
    );

    const shouldDisplayChannel = source !== null && attribute?.scopable;
    const shouldDisplayLocale = source !== null && attribute?.localizable && !attribute?.scopable;
    const shouldDisplayChannelLocale =
        source !== null && source.scope !== null && attribute?.localizable && attribute?.scopable;

    return (
        <>
            {target === null && <SourcePlaceholder />}
            {target && (
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
                        <SelectChannelDropdown source={source} onChange={onChange} error={errors?.scope} />
                    )}
                    {shouldDisplayLocale && (
                        <SelectLocaleDropdown source={source} onChange={onChange} error={errors?.locale} />
                    )}
                    {shouldDisplayChannelLocale && (
                        <SelectChannelLocaleDropdown source={source} onChange={onChange} error={errors?.locale} />
                    )}
                </>
            )}
        </>
    );
};
