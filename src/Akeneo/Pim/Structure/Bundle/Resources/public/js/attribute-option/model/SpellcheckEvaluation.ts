type LocalesSpellcheckEvaluation = {
  [locale: string]: boolean;
};

type OptionsSpellcheckEvaluation = {
  [option: string]: {
    count: number;
    toImprove: number;
    locales: LocalesSpellcheckEvaluation;
  };
};

type SpellcheckEvaluation = {
  attribute: string;
  options: OptionsSpellcheckEvaluation;
  options_count: number;
  labels: LocalesSpellcheckEvaluation;
  labels_count: number;
};

export {LocalesSpellcheckEvaluation, OptionsSpellcheckEvaluation, SpellcheckEvaluation};
