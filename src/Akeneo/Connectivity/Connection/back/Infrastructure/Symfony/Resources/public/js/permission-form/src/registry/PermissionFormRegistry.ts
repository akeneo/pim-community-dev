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

        const modules = Object.keys(config.providers)
            .sort((a, b) => {
                return (config.providers[a].order ?? 0) - (config.providers[b].order ?? 0);
            })
            .map(key => config.providers[key].module);

        return await Promise.all(modules.map(async (module): Promise<any> => {
            return (await requireContext(module)).default;
        }));
    },
};

export default PermissionFormRegistry;
