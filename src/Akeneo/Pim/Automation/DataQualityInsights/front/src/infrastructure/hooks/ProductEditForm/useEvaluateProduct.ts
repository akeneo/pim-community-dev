import {useCallback} from 'react';
import {Product} from '../../../domain';
import {useRoute} from '@akeneo-pim-community/shared';

const useEvaluateProduct = (product: Product) => {
  const routeName =
    'product_model' === product.meta.model_type
      ? 'akeneo_data_quality_insights_evaluate_product_model'
      : 'akeneo_data_quality_insights_evaluate_product';

  const url = useRoute(routeName, {productId: null !== product.meta.id ? product.meta.id.toString() : ''});

  return useCallback(async () => {
    await fetch(url, {
      method: 'POST',
      headers: [['X-Requested-With', 'XMLHttpRequest']],
    });
  }, [url]);
};

export {useEvaluateProduct};
