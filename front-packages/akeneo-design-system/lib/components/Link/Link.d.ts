import React, { ReactNode } from 'react';
declare type LinkProps = {
    disabled?: boolean;
    decorated?: boolean;
    children: ReactNode;
    target?: string;
    href?: string;
} & React.AnchorHTMLAttributes<HTMLAnchorElement>;
declare const Link: React.ForwardRefExoticComponent<{
    disabled?: boolean | undefined;
    decorated?: boolean | undefined;
    children: ReactNode;
    target?: string | undefined;
    href?: string | undefined;
} & React.AnchorHTMLAttributes<HTMLAnchorElement> & React.RefAttributes<HTMLAnchorElement>>;
export { Link };
export type { LinkProps };
