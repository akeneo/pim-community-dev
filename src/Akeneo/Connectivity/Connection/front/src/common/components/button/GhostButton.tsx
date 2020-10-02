import React, {forwardRef, Ref} from 'react';
import {Button, Props} from './Button';

export const GhostButton = forwardRef(({classNames = [], ...props}: Props, ref: Ref<HTMLButtonElement>) => {
    classNames.push('AknButton--ghost');

    return <Button {...props} ref={ref} classNames={classNames} />;
});
