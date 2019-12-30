import React from 'react';
import {PropsWithChildren} from 'react';
import {Button, Props} from './Button';

export const ApplyButton = ({classNames = [], ...props}: PropsWithChildren<Props>) => {
    classNames.push('AknButton--apply');

    return <Button {...props} classNames={classNames}></Button>;
};
