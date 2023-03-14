import {Attribute} from '../../../models/Attribute';
import {useSystemAttributes} from './useSystemAttributes';

export const useSystemAttribute = (code: string): Attribute | null => {
    const systemAttributes = useSystemAttributes();
    return systemAttributes.find(systemAttribute => systemAttribute.code === code) ?? null;
};
