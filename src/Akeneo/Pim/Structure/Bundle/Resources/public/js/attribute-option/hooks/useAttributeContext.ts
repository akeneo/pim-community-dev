import {useContext} from 'react';
import {AttributeContext} from '../contexts';

export const useAttributeContext = () => {
    const attributeContext = useContext(AttributeContext);
    if (!attributeContext) {
        throw new Error('[AttributeContext]: attribute context has not been properly initiated');
    }

    return attributeContext;
};
