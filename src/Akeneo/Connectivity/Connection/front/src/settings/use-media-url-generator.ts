import {useContext} from 'react';
import {RouterContext} from '../shared/router';

export const useMediaUrlGenerator = () => {
    const {generate} = useContext(RouterContext);

    return (path: string, filter = 'preview') => {
        const filename = encodeURIComponent(path);

        return generate('pim_enrich_media_show', {filename, filter});
    };
};
