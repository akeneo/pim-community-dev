import React, { ReactElement, ReactNode } from 'react';
import { IconProps } from '../../icons';
declare type Level = 'info' | 'warning' | 'error' | 'success';
declare type HelperProps = {
    inline?: boolean;
    level?: Level;
    icon?: ReactElement<IconProps>;
    children: ReactNode;
};
declare const Helper: React.ForwardRefExoticComponent<HelperProps & React.RefAttributes<HTMLDivElement>>;
export { Helper };
export type { HelperProps };
