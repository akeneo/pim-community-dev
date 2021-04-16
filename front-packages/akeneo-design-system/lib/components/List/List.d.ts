import React, { ReactNode } from 'react';
import { Override } from '../../shared';
declare type RemoveCellProps = React.HTMLAttributes<HTMLDivElement>;
declare type RowProps = Override<React.HTMLAttributes<HTMLDivElement>, {
    multiline?: boolean;
}>;
declare type CellProps = Override<React.HTMLAttributes<HTMLDivElement>, {
    width: 'auto' | number;
}>;
declare type ActionCellProps = React.HTMLAttributes<HTMLDivElement>;
declare type ListProps = {
    children?: ReactNode;
};
declare const List: {
    ({ children, ...rest }: ListProps): JSX.Element;
    Row: {
        ({ children, multiline }: RowProps): JSX.Element;
        displayName: string;
    };
    Cell: {
        ({ title, width, children, ...rest }: CellProps): JSX.Element;
        displayName: string;
    };
    TitleCell: import("styled-components").StyledComponent<"div", any, {
        width: 'auto' | number;
    } & Record<string, unknown> & import("styled-components").ThemeProps<import("../../theme/theme").Theme>, never>;
    ActionCell: {
        ({ children, ...rest }: ActionCellProps): JSX.Element;
        displayName: string;
    };
    RemoveCell: {
        ({ children, ...rest }: RemoveCellProps): JSX.Element;
        displayName: string;
    };
    RowHelpers: import("styled-components").StyledComponent<"div", any, {}, never>;
};
export { List };
