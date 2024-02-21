import React, {FC, useContext} from 'react';
import {LocaleContext} from '../../shared/locale/locale-context';

type Props = {
    locale: string;
};

const Flag: FC<Props> = ({locale}) => {
    const region = locale?.split('_')[locale.split('_').length - 1];
    const locales = useContext(LocaleContext);
    const foundLocaleLabel = locales?.find(loc => loc.code === locale);

    if (undefined !== foundLocaleLabel && 'string' === typeof region) {
        return (
            <>
                <i className={`flag flag-${region.toLowerCase()}`} /> {foundLocaleLabel.language}
            </>
        );
    }
    return <>{locale}</>;
};

export {Flag};
