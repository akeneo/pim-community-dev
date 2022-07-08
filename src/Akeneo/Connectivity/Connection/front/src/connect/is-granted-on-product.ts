import {ConnectedApp} from '../model/Apps/connected-app';

const isGrantedOnProduct: (connectedApp: ConnectedApp, level: 'view' | 'edit' | 'delete') => boolean = (
    connectedApp,
    level
) => {
    const grantedMapping = {
        view: ['read_products', 'write_products', 'delete_products'],
        edit: ['write_products', 'delete_products'],
        delete: ['delete_products'],
    };

    return connectedApp.scopes.find(scope => grantedMapping[level].includes(scope)) !== undefined;
};

export default isGrantedOnProduct;
