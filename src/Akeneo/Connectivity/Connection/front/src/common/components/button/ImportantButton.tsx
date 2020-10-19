import React, {forwardRef, Ref} from 'react';
import {Button, Props} from './Button';

export const ImportantButton = forwardRef(({classNames = [], ...props}: Props, ref: Ref<HTMLButtonElement>) => {
    classNames.push('AknButton--important');

    return <Button {...props} ref={ref} classNames={classNames}></Button>;
});
