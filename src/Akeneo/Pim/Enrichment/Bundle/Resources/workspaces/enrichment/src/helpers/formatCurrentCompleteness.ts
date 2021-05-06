import {CurrentCompleteness, LocaleCurrentCompleteness, MissingAttribute} from '../models';

type BackendMissingAttribute = {
  code: string;
  labels: {
    [localeCode: string]: string;
  };
};

type BackendLocaleCompleteness = {
  completeness: {
    missing: number;
    ratio: number;
  };
  missing: BackendMissingAttribute[];
  label: string;
};

type BackendCurrentCompleteness = {
  stats: {
    average: number;
  };
  locales: {
    [localeCode: string]: BackendLocaleCompleteness;
  };
};

type FormattedLocalesCompleteness = {
  [localeCode: string]: LocaleCurrentCompleteness;
};

const formatLocaleCompleteness = (
  backendLocaleCompleteness: BackendLocaleCompleteness,
  catalogLocale: string
): LocaleCurrentCompleteness => {
  return {
    label: backendLocaleCompleteness.label,
    ratio: backendLocaleCompleteness.completeness.ratio,
    missingCount: backendLocaleCompleteness.completeness.missing,
    missingAttributes: backendLocaleCompleteness.missing.reduce(
      (missingAttributes: MissingAttribute[], backendAttribute: BackendMissingAttribute): MissingAttribute[] => {
        missingAttributes.push({
          code: backendAttribute.code,
          label: backendAttribute.labels[catalogLocale],
        });
        return missingAttributes;
      },
      []
    ),
  };
};

const formatCurrentCompleteness = (
  backendCompleteness: BackendCurrentCompleteness,
  catalogLocale: string
): CurrentCompleteness => {
  let localesCompleteness: FormattedLocalesCompleteness = {};
  Object.entries(backendCompleteness.locales).map(
    ([localeCode, localeCompleteness]: [string, BackendLocaleCompleteness]) => {
      localesCompleteness[localeCode] = formatLocaleCompleteness(localeCompleteness, catalogLocale);
    }
  );

  return {
    channelRatio: backendCompleteness.stats.average,
    localesCompleteness,
  };
};

export {formatCurrentCompleteness};
