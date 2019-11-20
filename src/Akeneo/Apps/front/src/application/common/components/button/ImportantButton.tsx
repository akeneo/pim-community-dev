import React from 'react';
import {PropsWithChildren} from 'react';
import {Button, Props} from './Button';

export const ImportantButton = ({classNames = [], ...props}: PropsWithChildren<Props>) => {
    classNames.push('AknButton--important');

    return <Button {...props} classNames={classNames}></Button>;
};
