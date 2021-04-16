import React, { ReactElement } from 'react';
import { HelperProps, InputProps, LocaleProps } from '../../components';
declare type FieldChild = ReactElement<InputProps<unknown>> | ReactElement<HelperProps> | FieldChild[] | false | null;
declare type FieldProps = {
    label: string;
    incomplete?: boolean;
    locale?: ReactElement<LocaleProps> | string | null;
    channel?: string | null;
    requiredLabel?: string;
    fullWidth?: boolean;
    children: FieldChild;
};
declare const Field: React.ForwardRefExoticComponent<FieldProps & React.RefAttributes<HTMLDivElement>>;
export { Field };
export type { FieldProps };
