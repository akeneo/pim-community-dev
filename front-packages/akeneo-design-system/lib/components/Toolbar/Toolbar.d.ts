import React, { ReactNode } from 'react';
import { Override } from '../../shared';
declare type ToolbarProps = Override<React.HTMLAttributes<HTMLDivElement>, {
    isVisible?: boolean;
    children?: ReactNode;
}>;
declare const Toolbar: {
    ({ isVisible, children, ...rest }: ToolbarProps): JSX.Element;
    LabelContainer: import("styled-components").StyledComponent<"div", any, Record<string, unknown> & import("styled-components").ThemeProps<import("../../theme/theme").Theme>, never>;
    SelectionContainer: import("styled-components").StyledComponent<"div", any, {}, never>;
    ActionsContainer: import("styled-components").StyledComponent<"div", any, {}, never>;
};
export { Toolbar };
