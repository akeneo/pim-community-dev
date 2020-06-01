import {useEffect} from 'react';
import {useDispatch, useSelector} from 'react-redux';
import {AttributeOptionsState} from '../store/store';
import {initializeLocalesAction} from '../reducers';
import baseFetcher from '../fetchers/baseFetcher';
import {useRoute} from '@akeneo-pim-community/legacy-bridge';

const useLocales = () => {
    const dispatchAction = useDispatch();
    const route = useRoute('pim_enrich_locale_rest_index', {activated: 'true'});

    useEffect(() => {
        (async () => {
            const locales = await baseFetcher(route);
            dispatchAction(initializeLocalesAction(locales));
        })();
    }, []);

    return useSelector((state: AttributeOptionsState) => state.locales);
};

export default useLocales;
