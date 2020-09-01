import {useState, useCallback, useEffect, EffectCallback} from 'react';

// PIM-9372: In order to fix the browser cache issue,
// we need to keep track of the timestamps appended to the url in a global dict
// because the state is lost when switching page (between AM and PEF for instance)
const timestampDict: {[url: string]: number} = {};

const useRegenerate = (url: string): [boolean, EffectCallback, string] => {
  const [regenerate, setRegenerate] = useState(false);
  const dictKey = btoa(url);
  const doRegenerate = useCallback(() => {
    setRegenerate(true);
    timestampDict[dictKey] = Date.now();
  }, [setRegenerate]);

  useEffect(() => {
    if (regenerate) fetch(url, {method: 'POST', cache: 'no-cache'}).then(() => setRegenerate(false));
  }, [regenerate]);

  return [regenerate, doRegenerate, timestampDict[dictKey] ? `${url}&c=${timestampDict[dictKey]}` : url];
};

export {useRegenerate};
