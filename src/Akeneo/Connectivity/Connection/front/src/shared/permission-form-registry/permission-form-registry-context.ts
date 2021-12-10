import {createContext} from 'react';
import {PermissionFormRegistry} from './permission-form-registry.interface';

export const PermissionFormRegistryContext = createContext<PermissionFormRegistry>({
    all: () => Promise.resolve([]),
});
