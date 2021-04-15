import {useCallback, useState} from "react";

export type FetchStatus = 'idle' | 'error' | 'fetching' | 'fetched';

const useFetch = <T>(url: string, init?: RequestInit) => {
  const [data, setData] = useState<T | null>(null);
  const [status, setStatus] = useState<FetchStatus>('idle');
  const [error, setError] = useState<string | null>(null);

  const doFetch = useCallback(async () => {
    setStatus('fetching');

    try {
      const response = await fetch(url, init);
      const data: T = await response.json();

      setData(data);
      setStatus('fetched');
    } catch (e) {
      setData(null);
      setStatus('error');
      setError(e.message);
    }

  }, [url]);

  return {
    data,
    fetch: doFetch,
    status,
    error
  }

};

export {useFetch};
