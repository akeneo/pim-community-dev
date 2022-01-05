import {useCallback} from 'react';
import {Product} from '../../../domain';

const Routing = require('routing');

const useEvaluateProduct = (product: Product) => {
  const routeName =
    'product_model' === product.meta.model_type
      ? 'akeneo_data_quality_insights_evaluate_product_model'
      : 'akeneo_data_quality_insights_evaluate_product';
  const url = Routing.generate(routeName, {productId: product.meta.id});

  return useCallback(async () => {
    await fetch(url, {
      method: 'POST',
      headers: [['X-Requested-With', 'XMLHttpRequest']],
    });
  }, [url]);
};

export {useEvaluateProduct};
