import {useCallback, useMemo} from 'react';
import {Product} from '../../../domain';
import {useRouter} from '@akeneo-pim-community/shared';

const useEvaluateProduct = (product: Product) => {
  const router = useRouter();

  const url = useMemo(() => {
    const routeName =
    'product_model' === product.meta.model_type
      ? 'akeneo_data_quality_insights_evaluate_product_model'
      : 'akeneo_data_quality_insights_evaluate_product';

    return router.generate(routeName, {productId: null !== product.meta.id ? product.meta.id.toString() : ''})
  }, [router, product]);

  return useCallback(async () => {
    await fetch(url, {
      method: 'POST',
      headers: [['X-Requested-With', 'XMLHttpRequest']],
    });
  }, [url]);
};

export {useEvaluateProduct};
