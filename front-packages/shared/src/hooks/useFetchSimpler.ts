import { useCallback, useState } from 'react';


export interface FetchResultIdle {
  type: 'idle'
}

export interface FetchResultFetching {
  type: 'fetching'
}

export interface FetchResultError {
  type: 'error'
  message: string;
}

export interface FetchResultFetched<Payload> {
  type: 'fetched'
  payload: Payload;
}


export type FetchResult<Payload> = FetchResultIdle | FetchResultFetching | FetchResultError | FetchResultFetched<Payload>

export type FetchHookResult<FrontendPayload> = [result: FetchResult<FrontendPayload>, fetch: () => Promise<void>]

const useFetchSimpler = <BackendPayload, FrontendPayload = BackendPayload>(
  url: string,
  payloadConverter: (payload: BackendPayload) => FrontendPayload = (payload) => payload as unknown as FrontendPayload,
  init?: RequestInit
): FetchHookResult<FrontendPayload> => {

  const [result, setResult] = useState<FetchResult<FrontendPayload>>({ type: 'idle' });

  const doFetch = useCallback(async () => {
    setResult({ type: 'fetching' });

    try {
      const response = await fetch(url, init);
      const payload = await response.json() as BackendPayload;

      setResult({ type: 'fetched', payload: payloadConverter(payload) })
    } catch (e) {
      setResult({ type: 'error', message: e.message })
    }
  }, [url]);

  return [result, doFetch];
};

export { useFetchSimpler };
