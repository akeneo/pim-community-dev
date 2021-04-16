import React, { ReactNode } from 'react';
declare type DropdownProps = {
    children?: ReactNode;
};
declare const Dropdown: {
    ({ children, ...rest }: DropdownProps): JSX.Element;
    Overlay: {
        ({ verticalPosition, onClose, children }: {
            verticalPosition?: import("../../hooks/usePosition").VerticalPosition | undefined;
            onClose: () => void;
            children: React.ReactNode;
        }): JSX.Element;
        displayName: string;
    };
    Header: React.ForwardRefExoticComponent<Omit<React.HTMLAttributes<HTMLDivElement>, "children"> & {
        children: React.ReactNode;
    } & React.RefAttributes<HTMLDivElement>>;
    Item: React.ForwardRefExoticComponent<Omit<React.HTMLAttributes<HTMLDivElement>, "children" | "disabled"> & {
        disabled?: boolean | undefined;
        children: React.ReactNode;
    } & React.RefAttributes<HTMLDivElement>>;
    Title: React.ForwardRefExoticComponent<Omit<React.HTMLAttributes<HTMLDivElement>, "children"> & {
        children: React.ReactNode;
    } & React.RefAttributes<HTMLDivElement>>;
    ItemCollection: React.ForwardRefExoticComponent<Omit<React.HTMLAttributes<HTMLDivElement>, "children"> & {
        children: React.ReactNode;
    } & React.RefAttributes<HTMLDivElement>>;
};
export { Dropdown };
