import {useCallback, useEffect, useState} from 'react';

import {fetchTextAnalysis} from '../../../fetcher';
import {MistakeElement} from '../../../../application/helper';
import analyzeSpellingInterface from '../../../../application/helper/Spellcheck/analyzeSpelling.interface';
import {useMountedState} from '../useMountedState';
import refreshSpellingInterface from '../../../../application/helper/Spellcheck/refreshSpelling.interface';

export type SpellcheckAnalysisState = {
  analysis: MistakeElement[];
  isLoading: boolean;
  analyze: analyzeSpellingInterface;
  refreshAnalysis: refreshSpellingInterface;
};

const useFetchSpellcheckAnalysis = (content: string, locale: string, isActive: boolean): SpellcheckAnalysisState => {
  const [previousContent, setPreviousContent] = useState<null | string>(null);
  const [analysis, setAnalysis] = useState<MistakeElement[]>([]);
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const {isMounted} = useMountedState();

  const doAnalyze = useCallback(
    (content: string, locale: string) => {
      (async () => {
        setIsLoading(true);
        setPreviousContent(content);

        const data = await fetchTextAnalysis(content, locale);

        if (isMounted()) {
          setAnalysis(data);
        }

        setIsLoading(false);
      })();
    },
    [setIsLoading, setPreviousContent, setAnalysis]
  );

  const hasContentChangedSinceLastAnalysis = useCallback(() => {
    return content === null || content !== previousContent;
  }, [content, previousContent]);

  const refreshAnalysis = useCallback(() => {
    if (content.length > 0) {
      doAnalyze(content, locale);
    }
  }, [content, locale, doAnalyze]);

  useEffect(() => {
    if (isActive && content.length > 0) {
      if (hasContentChangedSinceLastAnalysis()) {
        doAnalyze(content, locale);
      }
    } else {
      setAnalysis([]);
      setPreviousContent(null);
    }
  }, [content, locale, isActive, doAnalyze]);

  return {
    analysis,
    isLoading,
    analyze: doAnalyze,
    refreshAnalysis,
  };
};

export default useFetchSpellcheckAnalysis;
