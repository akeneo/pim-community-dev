import React, {FC} from 'react';
import {Source} from '../../../models/Source';
import {Target} from '../../../models/Target';
import {SourceErrors} from '../../../models/SourceErrors';
import {useAssetAttribute} from '../../../hooks/useAssetAttribute';
import {useTranslate} from '@akeneo-pim-community/shared';
import {SourceAssetAttributeSettings} from './SourceAssetAttributeSettings';
import {SelectSourceAssetAttributeDropdown} from './SelectSourceAssetAttributeDropdown';
import {AssetAttribute} from '../../../models/AssetAttribute';

type Props = {
    source: Source;
    target: Target;
    errors: SourceErrors | null;
    onChange: (value: Source) => void;
    assetFamilyCode: string;
};

export const SourceAssetAttributeSelection: FC<Props> = ({source, target, errors, onChange, assetFamilyCode}) => {
    const translate = useTranslate();
    const {data: assetAttribute} = useAssetAttribute(source?.parameters?.asset_attribute ?? '');

    const handleSourceAssetAttributeSelection = (selectedAssetAttribute: AssetAttribute) =>
        onChange({...source, parameters: {...source.parameters, asset_attribute: selectedAssetAttribute.identifier}});

    return (
        <>
            <SelectSourceAssetAttributeDropdown
                selectedIdentifier={source?.parameters?.asset_attribute ?? ''}
                target={target}
                assetFamilyCode={assetFamilyCode}
                onChange={handleSourceAssetAttributeSelection}
                error={errors?.parameters?.asset_attribute}
            />
            {undefined !== assetAttribute && null !== source && (
                <SourceAssetAttributeSettings source={source} assetAttribute={assetAttribute} errors={errors} onChange={onChange} />
            )}
        </>
    );
};
