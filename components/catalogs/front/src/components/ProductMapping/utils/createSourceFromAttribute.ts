import {Attribute} from '../../../models/Attribute';
import {Source} from '../models/Source';

export const createSourceFromAttribute = (attribute: Attribute): Source => {
    const source = {
        source: attribute.code,
        locale: null,
        scope: null,
    };

    if (attribute.type === 'pim_catalog_simpleselect' || attribute.type === 'pim_catalog_multiselect') {
        return {...source, parameters: {label_locale: null}};
    }
    if (attribute.type === 'pim_catalog_price_collection') {
        return {...source, parameters: {currency: null}};
    }

    return source;
};
