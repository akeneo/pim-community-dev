import React, { ReactNode } from 'react';
import { Override } from '../../shared';
declare type CardGridProps = {
    size?: 'normal' | 'big';
};
declare const CardGrid: import("styled-components").StyledComponent<"div", any, CardGridProps & Record<string, unknown> & import("styled-components").ThemeProps<import("../../theme/theme").Theme>, never>;
declare type CardProps = Override<React.HTMLAttributes<HTMLDivElement>, {
    src: string | null;
    fit?: 'cover' | 'contain';
    isSelected?: boolean;
    disabled?: boolean;
    onSelect?: (isSelected: boolean) => void;
    stacked?: boolean;
    children: ReactNode;
}>;
declare const Card: {
    ({ src, fit, isSelected, onSelect, disabled, children, onClick, stacked, ...rest }: CardProps): JSX.Element;
    BadgeContainer: import("styled-components").StyledComponent<"div", any, {
        stacked: boolean;
    } & Record<string, unknown> & import("styled-components").ThemeProps<import("../../theme/theme").Theme>, never>;
};
export { Card, CardGrid };
