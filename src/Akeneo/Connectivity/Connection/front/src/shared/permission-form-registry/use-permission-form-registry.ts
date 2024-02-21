import {useContext} from 'react';
import {PermissionFormRegistryContext} from './permission-form-registry-context';

export const usePermissionFormRegistry = () => useContext(PermissionFormRegistryContext);
