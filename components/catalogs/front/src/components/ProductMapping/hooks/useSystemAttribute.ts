import {Attribute} from '../../../models/Attribute';
import {useSystemAttributes} from './useSystemAttributes';

export const useSystemAttribute = (code: string): Attribute | null => {
    const systemAttributes = useSystemAttributes();
    for (const systemAttribute of systemAttributes) {
        if (systemAttribute.code === code) {
            return systemAttribute;
        }
    }
    return null;
};
