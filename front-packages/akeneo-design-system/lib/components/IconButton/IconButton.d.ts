import React, { ReactElement } from 'react';
import { IconProps } from '../../icons';
import { ButtonProps } from '../../components/Button/Button';
import { Override } from '../../shared';
declare type IconButtonProps = Override<Omit<ButtonProps, 'children'>, {
    ghost?: boolean | 'borderless';
    icon: ReactElement<IconProps>;
    title: string;
}>;
declare const IconButton: React.ForwardRefExoticComponent<Omit<Omit<ButtonProps, "children">, "title" | "ghost" | "icon"> & {
    ghost?: boolean | "borderless" | undefined;
    icon: ReactElement<IconProps>;
    title: string;
} & React.RefAttributes<HTMLButtonElement>>;
export { IconButton };
export type { IconButtonProps };
