import {ProductMapping as ProductMappingType} from '../models/ProductMapping';
import {Source} from '../models/Source';

export const createTargetsFromProductMapping = (mapping: ProductMappingType): [string, Source][] => {
    const targets = Object.entries(mapping);

    // move UUID to the top
    const index = targets.findIndex(([target]) => target === 'uuid');
    const uuid = targets.splice(index, 1)[0];
    targets.unshift(uuid);

    return targets;
};
