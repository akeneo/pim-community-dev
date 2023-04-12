import React, {FC, useCallback} from 'react';
import {SelectChannelDropdown} from './SelectChannelDropdown';
import {SelectLocaleDropdown} from './SelectLocaleDropdown';
import {SelectChannelLocaleDropdown} from './SelectChannelLocaleDropdown';
import {Source} from '../../models/Source';
import {Attribute} from '../../../../models/Attribute';
import {SourceErrors} from '../../models/SourceErrors';
import {AssetAttributeSourceSelection} from './AssetAttributeSourceSelection/AssetAttributeSourceSelection';
import {Target} from '../../models/Target';
import {ArrowIcon, getColor} from 'akeneo-design-system';
import styled from 'styled-components';

const Bullet = styled(ArrowIcon)`
    margin-top: 10px;
    color: ${getColor('grey', 100)};
    width: 22px;
    height: 22px;
`;

const BulletLine = styled.div`
    display: flex;
    flex-direction: row;
    flex: auto;
    gap: 8px;
    margin-top: 5px;
    padding-left: 10px;
    max-width: 460px;
    align-items: flex-start;
`;

type Props = {
    source: Source;
    target: Target;
    attribute: Attribute;
    errors: SourceErrors | null;
    onChange: (value: Source) => void;
};

export const SourceSettings: FC<Props> = ({source, target, attribute, errors, onChange}) => {
    const onChangeMiddleware = useCallback(
        source => {
            if (
                (attribute.type === 'pim_catalog_simpleselect' || attribute.type === 'pim_catalog_multiselect') &&
                (undefined === source.parameters?.label_locale || null === source.parameters?.label_locale)
            ) {
                source = {...source, parameters: {...source.parameters, label_locale: source.locale ?? null}};
            }

            if (
                ['categories', 'family', 'status'].includes(source.source) &&
                (undefined === source.parameters?.label_locale || null === source.parameters?.label_locale)
            ) {
                source = {...source, parameters: {...source.parameters, label_locale: null}};
            }

            if (attribute?.type === 'pim_catalog_price_collection' && !(source.parameters.currency ?? false)) {
                source = {...source, parameters: {...source.parameters, currency: null}};
            }

            if (attribute?.type === 'pim_catalog_metric') {
                source = {...source, parameters: {...source.parameters, unit: attribute.default_measurement_unit}};
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
            {attribute.asset_family && (
                <AssetAttributeSourceSelection
                    source={source}
                    target={target}
                    onChange={onChangeMiddleware}
                    errors={errors}
                    assetFamilyIdentifier={attribute.asset_family}
                />
            )}
        </>
    );
};
