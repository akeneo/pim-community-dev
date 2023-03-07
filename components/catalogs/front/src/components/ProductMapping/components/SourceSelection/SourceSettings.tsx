import React, {FC, useCallback} from 'react';
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

type Props = {
    source: Source;
    attribute: Attribute;
    errors: SourceErrors | null;
    onChange: (value: Source) => void;
};

export const SourceSettings: FC<Props> = ({source, attribute, errors, onChange}) => {
    const onChangeMiddleware = useCallback(
        source => {
            if (
                (attribute.type === 'pim_catalog_simpleselect' || attribute.type === 'pim_catalog_multiselect') &&
                (undefined === source.parameters?.label_locale || null === source.parameters?.label_locale)
            ) {
                source = {...source, parameters: {...source.parameters, label_locale: source.locale ?? null}};
            }

            if (attribute?.type === 'pim_catalog_price_collection' && !(source.parameters.currency ?? false)) {
                source = {...source, parameters: {...source.parameters, currency: source.currency ?? null}};
            }
            onChange(source);
        },

        [onChange, attribute]
    );
    return (
        <>
            {attribute.scopable && (
                <BulletLine>
                    <Bullet />
                    <SelectChannelDropdown source={source} onChange={onChangeMiddleware} error={errors?.scope} />
                </BulletLine>
            )}
            {attribute.localizable && !attribute.scopable && (
                <BulletLine>
                    <Bullet />
                    <SelectLocaleDropdown source={source} onChange={onChangeMiddleware} error={errors?.locale} />
                </BulletLine>
            )}
            {attribute.localizable && attribute.scopable && (
                <BulletLine>
                    <Bullet />
                    <SelectChannelLocaleDropdown
                        source={source}
                        onChange={onChangeMiddleware}
                        error={errors?.locale}
                        disabled={source.scope === null}
                    />
                </BulletLine>
            )}
        </>
    );
};
