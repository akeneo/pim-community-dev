import React, {useState} from 'react';
import {useBackboneRouter} from '../../../dependenciesTools/hooks';
import {
  getAllCurrencies,
  IndexedCurrencies,
} from '../../../repositories/CurrencyRepository';

const useActiveCurrencies = () => {
  const router = useBackboneRouter();
  const [activeCurrencies, setActiveCurrencies] = useState<IndexedCurrencies>(
    {}
  );

  React.useEffect(() => {
    let mounted = true;
    const loadActiveCurrencies = async () => {
      const currencies = await getAllCurrencies(router);
      if (mounted) {
        setActiveCurrencies(currencies);
      }
    };
    loadActiveCurrencies();

    return () => {
      mounted = false;
    };
  }, []);

  return activeCurrencies;
};

export {useActiveCurrencies};
