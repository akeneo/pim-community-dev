import {useEffect, useRef} from 'react';
import {useDispatch, useSelector} from 'react-redux';
import {AttributeOptionsState} from '../store/store';
import {initializeAttributeOptionsAction} from '../reducers';
import baseFetcher from '../fetchers/baseFetcher';
import {useAttributeContext} from '../contexts';
import {useRoute} from '@akeneo-pim-community/legacy-bridge';

const useAttributeOptions = () => {
    const dispatchAction = useDispatch();
    const attribute = useAttributeContext();
    const route = useRoute('pim_enrich_attributeoption_index', {attributeId: attribute.attributeId.toString()});
    const attributeOptions = useSelector((state: AttributeOptionsState) => state.attributeOptions);
    const mountRef = useRef<boolean>(false);

    useEffect(() => {
        mountRef.current = true;

        return () => {
            mountRef.current = false;
        };
    }, []);

    useEffect(() => {
        (async () => {
            if (attributeOptions === null) {
                const options = await baseFetcher(route);

                if (mountRef.current) {
                    dispatchAction(initializeAttributeOptionsAction(options));
                }
            }
        })();
    }, [route, dispatchAction]);

    return attributeOptions;
};

export default useAttributeOptions;
