type MissingAttribute = {
  code: string;
  label: string;
};

type LocaleCurrentCompleteness = {
  label: string;
  ratio: number;
  missingCount: number;
  missingAttributes: MissingAttribute[];
};

type CurrentCompleteness = {
  channelRatio: number;
  localesCompleteness: {
    [localeCode: string]: LocaleCurrentCompleteness
  }
};

export {CurrentCompleteness, LocaleCurrentCompleteness, MissingAttribute};
