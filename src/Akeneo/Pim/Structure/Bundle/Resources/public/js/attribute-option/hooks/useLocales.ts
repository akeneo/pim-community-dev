import {useEffect, useRef, useState} from 'react';
import baseFetcher from '../fetchers/baseFetcher';
import {useRoute} from '@akeneo-pim-community/legacy-bridge';
import {Locale} from '../model';

const useLocales = () => {
    const [locales, setLocales] = useState<Locale[]>([]);
    const route = useRoute('pim_enrich_locale_rest_index', {activated: 'true'});
    const mountedRef = useRef<boolean>(false);

    useEffect(() => {
        mountedRef.current = true;

        return () => {
            mountedRef.current = false;
        };
    }, []);

    useEffect(() => {
        (async () => {
            const data = await baseFetcher(route);
            if (!mountedRef.current) {
                return;
            }

            setLocales(data);
        })();
    }, [route, mountedRef]);


    return locales;
};

export default useLocales;
