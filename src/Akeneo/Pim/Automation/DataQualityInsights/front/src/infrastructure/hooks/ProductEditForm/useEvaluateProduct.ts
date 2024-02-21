import {useCallback, useMemo} from 'react';
import {Product} from '../../../domain';
import {useRouter} from '@akeneo-pim-community/shared';

const useEvaluateProduct = (productOrProductModel: Product) => {
  const router = useRouter();

  const url = useMemo(() => {
    if (null === productOrProductModel.meta.id) {
      throw Error('Product uuid or product model id is not defined');
    }

    if (
      'product_model' !== productOrProductModel.meta.model_type &&
      'product' !== productOrProductModel.meta.model_type
    ) {
      throw Error('Invalid product type');
    }

    const routeName =
      'product_model' === productOrProductModel.meta.model_type
        ? 'akeneo_data_quality_insights_evaluate_product_model'
        : 'akeneo_data_quality_insights_evaluate_product';

    const productId = productOrProductModel.meta.id.toString();

    return router.generate(routeName, {productId});
  }, [router, productOrProductModel]);

  return useCallback(async () => {
    await fetch(url, {
      method: 'POST',
      headers: [['X-Requested-With', 'XMLHttpRequest']],
    });
  }, [url]);
};

export {useEvaluateProduct};
