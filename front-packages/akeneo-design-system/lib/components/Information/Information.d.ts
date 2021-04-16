import React, { ReactNode } from 'react';
declare type InformationProps = {
    illustration: ReactNode;
    title: ReactNode;
    children: ReactNode;
};
declare const Information: React.ForwardRefExoticComponent<InformationProps & React.RefAttributes<HTMLDivElement>>;
declare const HighlightTitle: import("styled-components").StyledComponent<"span", any, Record<string, unknown> & import("styled-components").ThemeProps<import("../../theme/theme").Theme>, never>;
export { Information, HighlightTitle };
