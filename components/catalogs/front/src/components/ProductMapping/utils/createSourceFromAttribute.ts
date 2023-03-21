import {Attribute} from '../../../models/Attribute';
import {Source} from '../models/Source';

export const createSourceFromAttribute = (attribute: Attribute): Source => {
    const source = {
        source: attribute.code,
        locale: null,
        scope: null,
    };

    if (
        attribute.type === 'categories' ||
        attribute.type === 'family' ||
        attribute.type === 'pim_catalog_simpleselect' ||
        attribute.type === 'pim_catalog_multiselect'
    ) {
        return {...source, parameters: {label_locale: null}};
    }

    if (attribute.type === 'pim_catalog_price_collection') {
        return {...source, parameters: {currency: null}};
    }

    if (attribute.type === 'pim_catalog_metric') {
        return {...source, parameters: {unit: attribute.default_measurement_unit}};
    }

    if (attribute.type === 'pim_catalog_asset_collection') {
        return {
            ...source,
            parameters: {
                sub_source: null,
                sub_scope: null,
                sub_locale: null,
            },
        };
    }

    return source;
};
