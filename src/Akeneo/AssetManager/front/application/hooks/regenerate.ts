import {useState, useCallback, useEffect, EffectCallback} from 'react';

const useRegenerate = (url: string): [boolean, EffectCallback] => {
  const [regenerate, setRegenerate] = useState(false);
  const doRegenerate = useCallback(() => setRegenerate(true), [setRegenerate]);

  useEffect(() => {
    if (regenerate) fetch(url, {method: 'POST', cache: 'no-cache'}).then(() => setRegenerate(false));
  }, [regenerate]);

  return [regenerate, doRegenerate];
};

export {useRegenerate};
