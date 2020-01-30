import {createContext} from 'react';
import {Translate} from './translate.interface';

export const TranslateContext = createContext<Translate>((id, placeholders) => {
    let translation = id;
    if (placeholders && Object.keys(placeholders).length > 0) {
        translation += '?' + new URLSearchParams(placeholders).toString();
    }
    return translation;
});
