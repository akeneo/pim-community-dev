import {useEffect, useState} from 'react';
import {fetchQualityScoreEvolution} from '../../fetcher';

type RawScoreEvolutionData = {
  data: {
    [date: string]: string | null;
  };
  average_rank: string;
};

const useFetchQualityScoreEvolution = (
  channel: string,
  locale: string,
  familyCode: string | null,
  categoryCode: string | null
) => {
  const [data, setData] = useState<RawScoreEvolutionData | null>(null);

  useEffect(() => {
    setData(null);
    (async () => {
      const result = await fetchQualityScoreEvolution(channel, locale, familyCode, categoryCode);
      setData(result);
    })();
  }, [channel, locale, familyCode, categoryCode]);

  return data;
};

export {useFetchQualityScoreEvolution, RawScoreEvolutionData};
