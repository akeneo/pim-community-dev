import { useState, useEffect } from 'react';
import { httpGet } from '../../../fetch';
import { generateUrl } from '../../../dependenciesTools/utils';
import { Router } from '../../../dependenciesTools';
import { Status } from '../../../rules.constants';
import { FormData } from '../edit-rules.types';
type CountFn = (x: CountError | CountPending | CountComplete) => void;

type CountError = { value: -1; status: Status.ERROR };
type CountPending = { value: -1; status: Status.PENDING };
type CountComplete = { value: number; status: Status.COMPLETE };

const countError: CountError = { value: -1, status: Status.ERROR };
const countPending: CountPending = { value: -1, status: Status.PENDING };
const countComplete = (x: number): CountComplete => {
  return { value: x, status: Status.COMPLETE };
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

const getProductsCountUrl = async (url: string, fn: CountFn) => {
  if (!url.length) {
    fn(countError);
  }
  fn(countPending);
  try {
    const response = await httpGet(url);
    if (response.ok) {
      const result = await response.json();
      fn(countComplete(Number(result.impacted_product_count)));
    } else {
      fn(countError);
    }
  } catch {
    fn(countError);
  }
};

const getProductsCountUrlWithDebounce = debounceFn(getProductsCountUrl, 400);

const createProductsCountUrl = (router: Router, form: FormData) => {
  return generateUrl(
    router,
    'pimee_enrich_rule_definition_get_impacted_product_count',
    { conditions: JSON.stringify(form?.content?.conditions || []) }
  );
};

const useProductsCount = (router: Router, formValues: FormData) => {
  const url = createProductsCountUrl(router, formValues);
  const [count, setCount] = useState<CountError | CountPending | CountComplete>(
    countPending
  );
  useEffect(() => {
    getProductsCountUrlWithDebounce(url, setCount);
  }, [url]);
  return count;
};

export { useProductsCount };
