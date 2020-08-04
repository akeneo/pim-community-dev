import {useState, useCallback, useEffect, EffectCallback} from 'react';

const useRegenerate = (url: string): [boolean, EffectCallback, string] => {
  const [regenerate, setRegenerate] = useState(false);
  const [generationCount, setGenerationCount] = useState<number>(0);
  const doRegenerate = useCallback(() => {
    setRegenerate(true);
    setGenerationCount(generationCount => generationCount + 1);
  }, [setRegenerate]);

  useEffect(() => {
    if (regenerate) fetch(url, {method: 'POST', cache: 'no-cache'}).then(() => setRegenerate(false));
  }, [regenerate]);

  return [regenerate, doRegenerate, 0 === generationCount ? url : `${url}&c=${generationCount}`];
};

export {useRegenerate};
