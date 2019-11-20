import React from 'react';
import {PropsWithChildren} from 'react';
import {Button, Props} from './Button';

export const GreyButton = ({classNames = [], ...props}: PropsWithChildren<Props>) => {
    classNames.push('AknButton--grey');

    return <Button {...props} classNames={classNames}></Button>;
};
