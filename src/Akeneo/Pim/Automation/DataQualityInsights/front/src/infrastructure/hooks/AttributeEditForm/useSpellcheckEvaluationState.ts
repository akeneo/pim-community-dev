import {useCallback, useState} from 'react';
import fetchSpellcheckEvaluation from '../../fetcher/AttributeEditForm/fetchSpellcheckEvaluation';
import {useMountedState} from '../Common/useMountedState';

export type LocalesSpellcheckEvaluation = {
  [locale: string]: boolean;
};

export type OptionsSpellcheckEvaluation = {
  [option: string]: {
    count: number;
    toImprove: number;
    locales: LocalesSpellcheckEvaluation;
  };
};

export type SpellcheckEvaluation = {
  attribute: string;
  options: OptionsSpellcheckEvaluation;
  options_count: number;
  labels: LocalesSpellcheckEvaluation;
  labels_count: number;
};

export type SpellcheckEvaluationState = {
  evaluation: SpellcheckEvaluation;
  refresh: () => Promise<void>;
};

export const initialSpellcheckEvaluationState = {
  attribute: '',
  options: {},
  options_count: 0,
  labels: {},
  labels_count: 0,
};

export const useSpellcheckEvaluationState = (attributeCode: string): SpellcheckEvaluationState => {
  const [spellcheckEvaluation, setSpellcheckEvaluation] = useState<SpellcheckEvaluation>({
    ...initialSpellcheckEvaluationState,
    attribute: attributeCode,
  });
  const {isMounted} = useMountedState();

  const refresh = useCallback(async () => {
    const response = fetchSpellcheckEvaluation(attributeCode);

    return response.then(evaluation => {
      if (isMounted()) {
        setSpellcheckEvaluation(evaluation);
      }
    });
  }, [setSpellcheckEvaluation, isMounted]);

  /*
  useEffect(() => {
    refresh();

  }, [attributeCode]);
*/
  return {
    evaluation: spellcheckEvaluation,
    refresh,
  };
};
