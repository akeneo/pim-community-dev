import {ReactNode} from 'react';

export interface PermissionFormProvider<T> {
    key: string;
    renderForm: (onChange: (state: T) => void) => ReactNode;
    renderPreview: (state: T) => ReactNode;
    save: (role: string, state: T) => boolean;
}

export interface PermissionFormRegistry {
    all: () => Promise<PermissionFormProvider<any>[]>;
}
