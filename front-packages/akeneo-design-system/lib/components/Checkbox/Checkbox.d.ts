import React, { ReactNode, SyntheticEvent } from 'react';
declare type CheckboxChecked = boolean | 'mixed';
declare const Checkbox: React.ForwardRefExoticComponent<Omit<React.HTMLAttributes<HTMLDivElement>, "children" | "onChange" | "checked" | "readOnly"> & {
    checked: CheckboxChecked;
    readOnly?: boolean | undefined;
    onChange?: ((value: boolean, event: SyntheticEvent) => void) | undefined;
    children?: ReactNode;
} & React.RefAttributes<HTMLDivElement>>;
export { Checkbox };
export type { CheckboxChecked };
