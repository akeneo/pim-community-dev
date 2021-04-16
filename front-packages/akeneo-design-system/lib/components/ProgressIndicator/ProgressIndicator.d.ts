import React, { ReactNode } from 'react';
import { Override } from '../../shared';
declare type StepState = 'done' | 'inprogress' | 'todo';
declare type ProgressIndicatorProps = Override<React.HTMLAttributes<HTMLUListElement>, {
    children?: ReactNode;
}>;
declare const ProgressIndicator: {
    ({ children, ...rest }: ProgressIndicatorProps): JSX.Element;
    Step: React.ForwardRefExoticComponent<Omit<React.HTMLAttributes<HTMLLIElement>, "children" | "current" | "state"> & {
        current?: boolean | undefined;
        state?: StepState | undefined;
        children: ReactNode;
    } & React.RefAttributes<HTMLLIElement>>;
};
export { ProgressIndicator };
