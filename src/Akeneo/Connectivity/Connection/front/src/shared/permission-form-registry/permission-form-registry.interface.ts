import {ReactNode} from 'react';

export interface PermissionFormProvider<T> {
    key: string;
    label: string;
    renderForm: (onChange: (state: T) => void, initialState: T | undefined) => ReactNode;
    renderSummary: (state: T) => ReactNode;
    save: (userGroup: string, state: T) => Promise<void>;
}

export interface PermissionFormRegistry {
    all: () => Promise<PermissionFormProvider<any>[]>;
}
