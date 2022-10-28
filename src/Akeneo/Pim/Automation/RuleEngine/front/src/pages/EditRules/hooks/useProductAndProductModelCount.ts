import {useEffect, useMemo, useState} from 'react';
import {useFormContext} from 'react-hook-form';
import {httpPost} from '../../../fetch';
import {generateUrl} from '../../../dependenciesTools/utils';
import {Router} from '../../../dependenciesTools';
import {Status} from '../../../rules.constants';
import {FormData} from '../edit-rules.types';
import {Condition} from '../../../models';
import {formatDateLocaleTimeConditionsToBackend} from '../components/conditions/DateConditionLines/dateConditionLines.utils';

type CountFn = (x: CountError | CountPending | CountComplete) => void;

type CountError = {
  productCount: -1;
  productModelCount: -1;
  status: Status.ERROR;
};
type CountPending = {
  productCount: -1;
  productModelCount: -1;
  status: Status.PENDING;
};
type CountComplete = {
  productCount: number;
  productModelCount: number;
  status: Status.COMPLETE;
};

const countError: CountError = {
  productCount: -1,
  productModelCount: -1,
  status: Status.ERROR,
};
const countPending: CountPending = {
  productCount: -1,
  productModelCount: -1,
  status: Status.PENDING,
};
const countComplete = (
  productCount: number,
  productModelCount: number
): CountComplete => {
  return {productCount, productModelCount, status: Status.COMPLETE};
};

const debounceFn = (fn: any, delay: number) => {
  let timeout: any;
  return (...args: any[]) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
      fn(...args);
    }, delay);
  };
};

const getProductsCountUrl = async (
  url: string,
  fn: CountFn,
  conditions: any[]
) => {
  if (!url.length) {
    fn(countError);
  }
  fn(countPending);
  try {
    const response = await httpPost(url, {
      body: {conditions: conditions},
    });
    if (response.ok) {
      const result = await response.json();
      fn(
        countComplete(
          Number(result.impacted_product_count),
          Number(result.impacted_product_model_count)
        )
      );
    } else {
      fn(countError);
    }
  } catch {
    fn(countError);
  }
};

const getProductsCountUrlWithDebounce = debounceFn(getProductsCountUrl, 400);

const isConditionValid = (condition: Condition) =>
  condition !== null &&
  Object.entries(condition).length &&
  Object.values(condition).some(value => value);

const useProductAndProductModelCount = (
  router: Router,
  formValues: FormData
) => {
  const url = generateUrl(
    router,
    'pimee_enrich_rule_definition_get_impacted_product_count'
  );
  const {watch} = useFormContext();
  const [count, setCount] = useState<CountError | CountPending | CountComplete>(
    countPending
  );
  // Watch allows to subscribe input's change via event listener. We need that to trigger a new products count.
  watch(`content.conditions`);

  const conditions = useMemo(() => {
    const filterConditions =
      formValues?.content?.conditions?.filter(isConditionValid) || [];
    const formattedConditions = formatDateLocaleTimeConditionsToBackend(
      filterConditions
    );
    return JSON.stringify(formattedConditions);
  }, [formValues.content.conditions]);

  useEffect(() => {
    getProductsCountUrlWithDebounce(url, setCount, conditions);
  }, [conditions, url]);
  return count;
};

export {useProductAndProductModelCount};
