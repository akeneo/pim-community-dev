import React, {FC} from 'react';
import {SelectChannelDropdown} from './SelectChannelDropdown';
import {SelectLocaleDropdown} from './SelectLocaleDropdown';
import {SelectChannelLocaleDropdown} from './SelectChannelLocaleDropdown';
import {Source} from '../../models/Source';
import {Attribute} from '../../../../models/Attribute';
import {SourceErrors} from '../../models/SourceErrors';
import {ArrowIcon, getColor} from 'akeneo-design-system';
import styled from 'styled-components';

const Bullet = styled(ArrowIcon)`
    color: ${getColor('grey', 100)};
`;

const BulletLine = styled.div`
    display: flex;
    flex-direction: row;
    flex: auto;
    gap: 8px;
    margin-top: 5px;
    padding-left: 10px;
    max-width: 460px;
    align-items: center;
`;

const prefillEmptySourceValue = (source: Source, attributeType: string) => {
    if (
        (attributeType === 'pim_catalog_simpleselect' || attributeType === 'pim_catalog_multiselect') &&
        (undefined === source.parameters?.label_locale || null === source.parameters?.label_locale)
    ) {
        return {...source, parameters: {...source.parameters, label_locale: source.locale ?? null}};
    }

    if (
        ['categories', 'family', 'status'].includes(source.source ?? '') &&
        (undefined === source.parameters?.label_locale || null === source.parameters?.label_locale)
    ) {
        return {...source, parameters: {...source.parameters, label_locale: null}};
    }

    if (attributeType === 'pim_catalog_price_collection' && !(source.parameters?.currency ?? false)) {
        return {...source, parameters: {...source.parameters, currency: null}};
    }

    return source;
};

type Props = {
    source: Source;
    attribute: Attribute;
    errors: SourceErrors | null;
    onChange: (value: Source) => void;
};

export const SourceSettings: FC<Props> = ({source, attribute, errors, onChange}) => {
    const onChangeMiddleware = (newSource: Source) => {
        onChange(prefillEmptySourceValue(newSource, attribute.type));
    };

    const handleChannelChange = (newChannel: string) =>
        onChangeMiddleware({
            ...source,
            scope: newChannel,
            locale: null,
        });

    const handleLocaleChange = (newLocale: string) => onChangeMiddleware({...source, locale: newLocale});

    return (
        <>
            {attribute.scopable && (
                <BulletLine>
                    <Bullet />
                    <SelectChannelDropdown
                        channel={source.scope}
                        onChange={handleChannelChange}
                        error={errors?.scope}
                    />
                </BulletLine>
            )}
            {attribute.localizable && !attribute.scopable && (
                <BulletLine>
                    <Bullet />
                    <SelectLocaleDropdown locale={source.locale} onChange={handleLocaleChange} error={errors?.locale} />
                </BulletLine>
            )}
            {attribute.localizable && attribute.scopable && (
                <BulletLine>
                    <Bullet />
                    <SelectChannelLocaleDropdown
                        channel={source.scope}
                        locale={source.locale}
                        onChange={handleLocaleChange}
                        error={errors?.locale}
                        disabled={source.scope === null}
                    />
                </BulletLine>
            )}
        </>
    );
};
