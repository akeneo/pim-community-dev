import {ReactNode} from 'react';
import requireContext from '../dependencies/require-context';

type ModuleConfig = {
    providers: {
        [key: string]: {
            module: string;
            order?: number;
        };
    }
}

export interface PermissionFormProvider<T> {
    key: string;
    render: (onChange: (state: T) => void) => ReactNode;
    save: (role: string, state: T) => boolean;
}

const PermissionFormRegistry = {
    _getModuleConfig: /* istanbul ignore next */ (): ModuleConfig => {
        // @ts-ignore
        return __moduleConfig;
    },
    all: async (): Promise<PermissionFormProvider<any>[]> => {
        const config = PermissionFormRegistry._getModuleConfig();
        const providers = config.providers || {};

        const modules = Object.keys(providers)
            .sort((a, b) => {
                return (providers[a].order ?? 0) - (providers[b].order ?? 0);
            })
            .map(key => providers[key].module);

        return await Promise.all(modules.map(async (module): Promise<any> => {
            return (await requireContext(module)).default;
        }));
    },
};

export default PermissionFormRegistry;
