export interface ProductQualityScore {
  [channel: string]: {
    [locale: string]: string | null;
  };
}
