import {useCallback, useEffect, useState} from 'react';
import fetchSpellcheckEvaluation from "../../fetcher/AttributeEditForm/fetchSpellcheckEvaluation";
import {useMountedState} from "../Common/useMountedState";

type LocalesSpellcheckEvaluation = {
  [locale: string]: boolean;
};

type OptionsSpellcheckEvaluation = {
  [option: string]: {
    count: number;
    toImprove: number;
    locales: LocalesSpellcheckEvaluation;
  }
};

type SpellcheckEvaluation = {
  attribute: string;
  options: OptionsSpellcheckEvaluation;
  options_count: number;
  labels: LocalesSpellcheckEvaluation;
  labels_count: number;
}

export type SpellcheckEvaluationState = {
  evaluation: SpellcheckEvaluation;
  refresh: () => Promise<void>;
}

export const initialSpellcheckEvaluationState = {
  attribute: "",
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
    if (!isMounted()) {
      return;
    }

    const evaluation = await fetchSpellcheckEvaluation(attributeCode);
    setSpellcheckEvaluation(evaluation);
  }, [attributeCode, isMounted, setSpellcheckEvaluation]);

  useEffect(() => {
    (async () => refresh())();
  }, [attributeCode]);

  return {
    evaluation: spellcheckEvaluation,
    refresh,
  };
};
