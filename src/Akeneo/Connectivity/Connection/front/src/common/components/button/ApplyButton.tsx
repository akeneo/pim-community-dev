import React, {forwardRef, Ref} from 'react';
import {Button, Props} from './Button';

export const ApplyButton = forwardRef(({classNames = [], ...props}: Props, ref: Ref<HTMLButtonElement>) => {
    classNames.push('AknButton--apply');

    return <Button {...props} ref={ref} classNames={classNames}></Button>;
});
