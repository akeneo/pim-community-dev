import React, { ReactNode } from 'react';
import { Override } from '../../shared';
declare type SectionTitleProps = Override<React.HTMLAttributes<HTMLDivElement>, {
    sticky?: number;
    children?: ReactNode;
}>;
declare const SectionTitle: {
    ({ children, ...rest }: SectionTitleProps): JSX.Element;
    Title: import("styled-components").StyledComponent<"h2", any, Record<string, unknown> & import("styled-components").ThemeProps<import("../../theme/theme").Theme>, never>;
    Spacer: import("styled-components").StyledComponent<"div", any, {}, never>;
    Separator: import("styled-components").StyledComponent<"div", any, Record<string, unknown> & import("styled-components").ThemeProps<import("../../theme/theme").Theme>, never>;
    Information: import("styled-components").StyledComponent<"div", any, Record<string, unknown> & import("styled-components").ThemeProps<import("../../theme/theme").Theme>, never>;
};
export { SectionTitle };
