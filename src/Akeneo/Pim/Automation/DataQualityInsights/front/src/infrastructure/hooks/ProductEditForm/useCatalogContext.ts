import {useSelector} from 'react-redux';
import {ProductEditFormState} from '../../store';

const useCatalogContext = () => {
  const {locale, channel} = useSelector((state: ProductEditFormState) => state.catalogContext);

  return {
    locale,
    channel,
  };
};

export default useCatalogContext;
