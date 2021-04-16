import React, { ReactNode } from 'react';
declare const ItemLabel: import("styled-components").StyledComponent<"span", any, {}, never>;
declare const Item: React.ForwardRefExoticComponent<Omit<React.HTMLAttributes<HTMLDivElement>, "children" | "disabled"> & {
    disabled?: boolean | undefined;
    children: ReactNode;
} & React.RefAttributes<HTMLDivElement>>;
export { Item, ItemLabel };
