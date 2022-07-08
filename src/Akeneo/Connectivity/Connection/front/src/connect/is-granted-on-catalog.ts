import {ConnectedApp} from '../model/Apps/connected-app';

const isGrantedOnCatalog: (connectedApp: ConnectedApp, level: 'view' | 'edit' | 'delete') => boolean = (
    connectedApp,
    level
) => {
    const grantedMapping = {
        view: ['read_catalogs', 'write_catalogs', 'delete_catalogs'],
        edit: ['write_catalogs', 'delete_catalogs'],
        delete: ['delete_catalogs'],
    };

    return connectedApp.scopes.find(scope => grantedMapping[level].includes(scope)) !== undefined;
};

export default isGrantedOnCatalog;
