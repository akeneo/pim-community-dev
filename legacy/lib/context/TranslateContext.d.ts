import React, { ReactNode } from 'react';
declare type Translator = (id: string, placeholders?: {
    [name: string]: string;
}, count?: number) => string;
declare const TranslateContext: React.Context<(id: string) => string>;
declare const useTranslate: () => Translator;
declare const TranslateProvider: ({ value, children }: {
    value: Translator;
    children: ReactNode;
}) => JSX.Element;
export { useTranslate, TranslateContext, TranslateProvider };
