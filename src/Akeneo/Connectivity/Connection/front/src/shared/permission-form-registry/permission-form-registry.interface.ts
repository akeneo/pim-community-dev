import {ReactNode} from 'react';

export interface PermissionFormProvider<T> {
    key: string;
    renderForm: (onChange: (state: T) => void, initialState: T | undefined) => ReactNode;
    renderSummary: (state: T) => ReactNode;
    save: (role: string, state: T) => boolean;
}

export interface PermissionFormRegistry {
    all: () => Promise<PermissionFormProvider<any>[]>;
    countProviders: () => number;
}
