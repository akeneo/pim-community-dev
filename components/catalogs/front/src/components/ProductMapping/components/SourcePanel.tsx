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
    const shouldDisplayNoParametersMessage = !(
        shouldDisplayLocale ||
        shouldDisplayChannel ||
        shouldDisplayChannelLocale
    );

    return (
        <>
            {null === target && <SourcePlaceholder />}
            {null !== target && 'uuid' === target.code && <SourceUuidPlaceholder targetLabel={target.label} />}
            {null !== target && 'uuid' !== target.code && (
                <>
                    <SectionTitle>
                        <SectionTitle.Title>{target.label}</SectionTitle.Title>
                    </SectionTitle>
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
                        <SelectChannelDropdown source={source} onChange={onChange} error={errors?.scope} />
                    )}
                    {shouldDisplayLocale && (
                        <SelectLocaleDropdown source={source} onChange={onChange} error={errors?.locale} />
                    )}
                    {shouldDisplayChannelLocale && (
                        <SelectChannelLocaleDropdown source={source} onChange={onChange} error={errors?.locale} />
                    )}
                    {shouldDisplayNoParametersMessage && (
                        <Information key={'no_parameters'}>
                            {translate('akeneo_catalogs.product_mapping.source.parameters.no_parameters_message')}
                        </Information>
                    )}
                </>
            )}
        </>
    );
};
