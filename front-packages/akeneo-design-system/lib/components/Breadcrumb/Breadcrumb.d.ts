import React, { ReactElement } from 'react';
import { LinkProps } from '../../components/Link/Link';
declare type BreadcrumbProps = {
    children: ReactElement<LinkProps> | ReactElement<LinkProps>[];
};
declare const Breadcrumb: {
    ({ children, ...rest }: BreadcrumbProps): JSX.Element;
    Step: import("styled-components").StyledComponent<React.ForwardRefExoticComponent<{
        disabled?: boolean | undefined;
        decorated?: boolean | undefined;
        children: React.ReactNode;
        target?: string | undefined;
        href?: string | undefined;
    } & React.AnchorHTMLAttributes<HTMLAnchorElement> & React.RefAttributes<HTMLAnchorElement>>, any, Record<string, unknown> & import("styled-components").ThemeProps<import("../../theme/theme").Theme>, never>;
};
export { Breadcrumb };
