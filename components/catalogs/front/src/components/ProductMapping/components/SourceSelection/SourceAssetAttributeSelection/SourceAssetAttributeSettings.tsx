import React, {FC, useCallback} from 'react';
import {Source} from '../../../models/Source';
import {Attribute} from '../../../../../models/Attribute';
import {SourceErrors} from '../../../models/SourceErrors';
import {AssetAttribute} from '../../../models/AssetAttribute';

type Props = {
    source: Source;
    assetAttribute: AssetAttribute
    errors: SourceErrors | null;
    onChange: (value: Source) => void;
};

export const SourceAssetAttributeSettings: FC<Props> = ({source, assetAttribute, errors, onChange}) => {
    return <></>;
};
