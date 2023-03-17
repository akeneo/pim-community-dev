import React, {FC, useCallback} from 'react';
import {Source} from '../../../models/Source';
import {Attribute} from '../../../../../models/Attribute';
import {SourceErrors} from '../../../models/SourceErrors';
import {AssetAttribute} from '../../../models/AssetAttribute';
import {SelectAssetAttributeChannelDropdown} from './SelectAssetAttributeChannelDropdown';
import {SelectAssetAttributeLocaleDropdown} from './SelectAssetAttributeLocaleDropdown';
import {SelectAssetAttributeChannelLocaleDropdown} from './SelectAssetAttributeChannelLocaleDropdown';

type Props = {
    source: Source;
    assetAttribute: AssetAttribute
    errors: SourceErrors | null;
    onChange: (value: Source) => void;
};

export const AssetAttributeSourceSettings: FC<Props> = ({source, assetAttribute, errors, onChange}) => {
    return <>
        {assetAttribute.scopable && (
            <SelectAssetAttributeChannelDropdown source={source} onChange={onChange} error={errors?.parameters?.sub_scope} />
        )}
        {assetAttribute.localizable && !assetAttribute.scopable && (
            <SelectAssetAttributeLocaleDropdown source={source} onChange={onChange} error={errors?.parameters?.sub_locale} />
        )}
        {assetAttribute.localizable && assetAttribute.scopable && (
            <SelectAssetAttributeChannelLocaleDropdown
                source={source}
                onChange={onChange}
                error={errors?.parameters?.sub_locale}
                disabled={source.parameters?.sub_scope === null}
            />
        )}
    </>;
};
