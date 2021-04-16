import React, { ReactNode } from 'react';
declare type SwitcherButtonProps = {
    label: string;
    onClick?: () => void;
    inline?: boolean;
    deletable?: boolean;
    onDelete?: () => void;
    children?: ReactNode;
};
declare const SwitcherButton: React.ForwardRefExoticComponent<SwitcherButtonProps & React.RefAttributes<HTMLDivElement>>;
export { SwitcherButton };
