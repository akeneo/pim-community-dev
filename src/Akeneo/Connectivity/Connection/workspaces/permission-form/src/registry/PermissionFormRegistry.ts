import {ReactNode} from 'react';
import requireContext from '../dependencies/require-context';

type ModuleConfig = {
    providers: {
        [key: string]: {
            module: string;
            order?: number;
        };
    };
};

export interface PermissionFormProvider<T> {
    key: string;
    label: string;
    renderForm: (
        onPermissionsChange: (state: T) => void,
        initialState: T | undefined,
        readOnly: boolean | undefined,
        onlyDisplayViewPermissions: boolean
    ) => ReactNode;
    renderSummary: (state: T, onlyDisplayViewPermissions: boolean) => ReactNode;
    save: (userGroup: string, state: T) => Promise<void>;
    loadPermissions: (userGroup: string) => Promise<T>;
}

let _config: ModuleConfig = {
    providers: {},
};

const PermissionFormRegistry = {
    setModuleConfig: (config: ModuleConfig) => {
        _config = config;
    },
    all: async (): Promise<PermissionFormProvider<any>[]> => {
        const providers = _config.providers || /* istanbul ignore next */ {};

        const modules = Object.keys(providers)
            .sort((a, b) => {
                return (providers[a].order ?? 0) - (providers[b].order ?? 0);
            })
            .map(key => providers[key].module);

        return await Promise.all(
            modules.map(async (module): Promise<any> => {
                return (await requireContext(module)).default;
            })
        );
    },
};

export default PermissionFormRegistry;
