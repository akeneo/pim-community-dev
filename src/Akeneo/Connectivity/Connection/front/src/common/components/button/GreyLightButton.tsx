import React from 'react';
import {PropsWithChildren} from 'react';
import {Button, Props} from './Button';

export const GreyLightButton = ({classNames = [], ...props}: PropsWithChildren<Props>) => {
    classNames.push('AknButton--greyLight');

    return <Button {...props} classNames={classNames}></Button>;
};
