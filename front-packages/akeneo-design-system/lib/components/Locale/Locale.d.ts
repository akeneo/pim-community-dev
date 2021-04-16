import React from 'react';
declare type LocaleProps = {
    code: string;
    languageLabel?: string;
};
declare const Locale: React.ForwardRefExoticComponent<LocaleProps & React.RefAttributes<HTMLSpanElement>>;
export { Locale };
export type { LocaleProps };
