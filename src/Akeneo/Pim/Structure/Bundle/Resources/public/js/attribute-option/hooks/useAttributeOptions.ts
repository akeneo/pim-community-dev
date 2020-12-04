import {useEffect} from 'react';
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
  let attributeOptions = useSelector((state: AttributeOptionsState) => state.attributeOptions);

  useEffect(() => {
    (async () => {
      if (attributeOptions === null) {
        attributeOptions = await baseFetcher(route);
        dispatchAction(initializeAttributeOptionsAction(attributeOptions));
      }
    })();
  }, []);

  return attributeOptions;
};

export default useAttributeOptions;
