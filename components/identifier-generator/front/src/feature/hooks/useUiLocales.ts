import {useQuery} from 'react-query';
import {UiLocale} from '../models';

const useUiLocales: () => {
  data?: UiLocale[];
  error: Error | null;
  isSuccess: boolean;
} = () => {
  const getUiLocales = async () => {
    return fetch('/system/locale/ui', {
      method: 'GET',
      headers: [['X-Requested-With', 'XMLHttpRequest']],
    }).then(res => {
      if (!res.ok) throw new Error(res.statusText);
      return res.json();
    });
  };

  const {error, data, isSuccess} = useQuery<UiLocale[], Error, UiLocale[]>('getUiLocales', getUiLocales, {
    keepPreviousData: true,
    refetchOnWindowFocus: false,
    retry: false,
  });

  return {data, error, isSuccess};
};

export {useUiLocales};
