import * as React from 'react';
import {PropsWithChildren} from 'react';
import { Button, Props } from './Button';

export const ActionButton = ({classNames = [], ...props}: PropsWithChildren<Props>) => {
    classNames.push('AknButton--action');

    return <Button {...props} classNames={classNames}></Button>;
};
