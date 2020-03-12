import {createContext} from 'react';

type TranslateContextValue = (id: string, placeholders?: {[name: string]: string}, count?: number) => string;

const TranslateContext = createContext<TranslateContextValue>((id, placeholders) => {
  let translation = id;
  if (placeholders && Object.keys(placeholders).length > 0) {
    translation += '?' + new URLSearchParams(placeholders).toString();
  }
  return translation;
});

export {TranslateContextValue, TranslateContext};
