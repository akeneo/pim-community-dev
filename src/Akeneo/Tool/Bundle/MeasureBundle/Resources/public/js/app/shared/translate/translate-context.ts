import {createContext} from 'react';

export type TranslateContextValue = (id: string, placeholders?: {[name: string]: string}, count?: number) => string;

export const TranslateContext = createContext<TranslateContextValue>((id, placeholders) => {
    let translation = id;
    if (placeholders && Object.keys(placeholders).length > 0) {
        translation += '?' + new URLSearchParams(placeholders).toString();
    }
    return translation;
});
