import { useCallback, useState } from 'react';


interface FetchResultIdle {
  type: 'idle'
}

interface FetchResultFetching {
  type: 'fetching'
}

interface FetchResultError {
  type: 'error'
  message: string;
}

interface FetchResultFetched<Payload> {
  type: 'fetched'
  payload: Payload;
}


type FetchResult<Payload> = FetchResultIdle | FetchResultFetching | FetchResultError | FetchResultFetched<Payload>

const useFetchSimpler = <BackendPayload, FrontendPayload = BackendPayload>(
  url: string,
  payloadConverter: (payload: BackendPayload) => FrontendPayload = (payload) => payload as unknown as FrontendPayload,
  init?: RequestInit
): [result: FetchResult<FrontendPayload>, fetch: () => Promise<void>] => {

  const [result, setResult] = useState<FetchResult<FrontendPayload>>({ type: 'idle' });

  const doFetch = useCallback(async () => {
    setResult({ type: 'fetching' });

    try {
      const response = await fetch(url, init);
      const payload = await response.json() as BackendPayload;

      setResult({ type: 'fetched', payload: payloadConverter(payload) })
    } catch ({ message }) {
      setResult({ type: 'error', message })
    }
  }, [url]);

  return [result, doFetch];
};

export { useFetchSimpler };
