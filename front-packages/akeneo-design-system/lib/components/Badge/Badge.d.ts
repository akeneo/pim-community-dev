import React, { ReactNode } from 'react';
import { Override } from '../../shared';
import { Level } from '../../theme';
declare type BadgeProps = Override<React.HTMLAttributes<HTMLSpanElement>, {
    level?: Level;
    children?: ReactNode;
}>;
declare const Badge: React.ForwardRefExoticComponent<Omit<React.HTMLAttributes<HTMLSpanElement>, "children" | "level"> & {
    level?: Level | undefined;
    children?: ReactNode;
} & React.RefAttributes<HTMLSpanElement>>;
export { Badge };
export type { BadgeProps };
