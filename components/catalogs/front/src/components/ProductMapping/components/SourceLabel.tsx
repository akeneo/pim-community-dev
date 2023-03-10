import React, {FC} from 'react';
import {useAttribute} from '../../../hooks/useAttribute';
import {useSystemAttribute} from '../hooks/useSystemAttribute';

export const SourceLabel: FC<{sourceCode: string}> = ({sourceCode}) => {
    const {data: standardAttribute} = useAttribute(sourceCode);
    const systemAttribute = useSystemAttribute(sourceCode);
    const attribute = systemAttribute ?? standardAttribute;
    return <>{attribute?.label ?? `[${sourceCode}]`}</>;
};
