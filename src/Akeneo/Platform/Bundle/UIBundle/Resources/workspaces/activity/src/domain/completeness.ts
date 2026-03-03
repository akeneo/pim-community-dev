type BackendCompletenessData = {
  [channel: string]: BackendChannelData;
};

type BackendChannelData = {
  labels: {
    [localeCode: string]: string | null;
  };
  total: number;
  complete: number;
  locales: {
    [localeTranslation: string]: number;
  };
};

export {BackendChannelData, BackendCompletenessData};
