import React from 'react';
import {PropsWithChildren} from 'react';
import {Button, Props} from './Button';

export const GhostButton = ({classNames = [], ...props}: PropsWithChildren<Props>) => {
    classNames.push('AknButton--ghost');

    return <Button {...props} classNames={classNames}></Button>;
};
