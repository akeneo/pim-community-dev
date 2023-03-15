import React, {FC} from 'react';
import {useAttribute} from '../../../hooks/useAttribute';

export const SourceLabel: FC<{sourceCode: string}> = ({sourceCode}) => {
    const {data: attribute} = useAttribute(sourceCode);
    return <>{attribute?.label ?? `[${sourceCode}]`}</>;
};
