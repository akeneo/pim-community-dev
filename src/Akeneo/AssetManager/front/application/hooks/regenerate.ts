import {useState, useCallback, useEffect, EffectCallback} from 'react';
import PQueue from 'p-queue';

const queue = new PQueue({concurrency: 1});

const useRegenerate = (url: string): [boolean, EffectCallback, string] => {
  const [regenerate, setRegenerate] = useState(false);
  const [generationCount, setGenerationCount] = useState<number>(0);
  const doRegenerate = useCallback(() => {
    setRegenerate(true);
    setGenerationCount(generationCount => generationCount + 1);
  }, [setRegenerate]);

  useEffect(() => {
    if (regenerate)
      queue.add(async () => fetch(url, {method: 'POST', cache: 'no-cache'}).then(() => setRegenerate(false)));
  }, [regenerate]);

  return [regenerate, doRegenerate, 0 === generationCount ? url : `${url}&c=${Math.floor(Math.random() * 10000)}`];
};

export {useRegenerate};
