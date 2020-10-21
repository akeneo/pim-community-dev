import React, {forwardRef, Ref} from 'react';
import {Button, Props} from './Button';

export const GreyButton = forwardRef(({classNames = [], ...props}: Props, ref: Ref<HTMLButtonElement>) => {
    classNames.push('AknButton--grey');

    return <Button {...props} ref={ref} classNames={classNames}></Button>;
});
