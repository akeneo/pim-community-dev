import React, {FC} from 'react';
import {Source} from '../../../models/Source';
import {Target} from '../../../models/Target';
import {SourceErrors} from '../../../models/SourceErrors';
import {useAssetAttribute} from '../../../hooks/useAssetAttribute';
import {AssetAttributeSourceSettings} from './AssetAttributeSourceSettings';
import {SelectAssetAttributeSourceDropdown} from './SelectAssetAttributeSourceDropdown';
import {AssetAttribute} from '../../../models/AssetAttribute';

type Props = {
    source: Source;
    target: Target;
    errors: SourceErrors | null;
    onChange: (value: Source) => void;
    assetFamilyIdentifier: string;
};

export const AssetAttributeSourceSelection: FC<Props> = ({source, target, errors, onChange, assetFamilyIdentifier}) => {
    const {data: assetAttribute} = useAssetAttribute(source?.parameters?.sub_source ?? '');

    const handleAssetAttributeSourceSelection = (selectedAssetAttribute: AssetAttribute) =>
        onChange({
            ...source,
            parameters: {
                sub_source: selectedAssetAttribute.identifier,
                sub_scope: null,
                sub_locale: null,
            },
        });

    return (
        <>
            <SelectAssetAttributeSourceDropdown
                selectedIdentifier={source?.parameters?.sub_source ?? ''}
                target={target}
                assetFamilyIdentifier={assetFamilyIdentifier}
                onChange={handleAssetAttributeSourceSelection}
                error={errors?.parameters?.sub_source}
            />
            {undefined !== assetAttribute && null !== source && (
                <AssetAttributeSourceSettings
                    source={source}
                    assetAttribute={assetAttribute}
                    errors={errors}
                    onChange={onChange}
                />
            )}
        </>
    );
};
