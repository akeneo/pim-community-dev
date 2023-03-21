import React, {FC} from 'react';
import {Source} from '../../../models/Source';
import {SourceErrors} from '../../../models/SourceErrors';
import {AssetAttribute} from '../../../models/AssetAttribute';
import {SelectAssetAttributeChannelDropdown} from './SelectAssetAttributeChannelDropdown';
import {SelectAssetAttributeLocaleDropdown} from './SelectAssetAttributeLocaleDropdown';
import {SelectAssetAttributeChannelLocaleDropdown} from './SelectAssetAttributeChannelLocaleDropdown';
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
    assetAttribute: AssetAttribute;
    errors: SourceErrors | null;
    onChange: (value: Source) => void;
};

export const AssetAttributeSourceSettings: FC<Props> = ({source, assetAttribute, errors, onChange}) => {
    return (
        <>
            {assetAttribute.scopable && (
                <BulletLine>
                    <Bullet />
                    <SelectAssetAttributeChannelDropdown
                        source={source}
                        onChange={onChange}
                        error={errors?.parameters?.sub_scope}
                    />
                </BulletLine>
            )}
            {assetAttribute.localizable && !assetAttribute.scopable && (
                <BulletLine>
                    <Bullet />
                    <SelectAssetAttributeLocaleDropdown
                        source={source}
                        onChange={onChange}
                        error={errors?.parameters?.sub_locale}
                    />
                </BulletLine>
            )}
            {assetAttribute.localizable && assetAttribute.scopable && (
                <BulletLine>
                    <Bullet />
                    <SelectAssetAttributeChannelLocaleDropdown
                        source={source}
                        onChange={onChange}
                        error={errors?.parameters?.sub_locale}
                        disabled={source.parameters?.sub_scope === null}
                    />
                </BulletLine>
            )}
        </>
    );
};
