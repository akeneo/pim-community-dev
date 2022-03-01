export interface QualityScore {
  [channel: string]: {
    [locale: string]: string | null;
  };
}
