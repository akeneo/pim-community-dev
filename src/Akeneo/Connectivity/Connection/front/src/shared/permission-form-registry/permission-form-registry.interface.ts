import {ReactNode} from 'react';

export interface PermissionFormProvider<T> {
    key: string;
    label: string;
    renderForm: (onChange: (state: T) => void, initialState: T | undefined) => ReactNode;
    renderSummary: (state: T) => ReactNode;
    save: (userGroup: string, state: T) => Promise<void>;
    loadPermissions: (userGroup: string) => Promise<T>;
}

export interface PermissionFormRegistry {
    all: () => Promise<PermissionFormProvider<any>[]>;
    count: () => number;
}
