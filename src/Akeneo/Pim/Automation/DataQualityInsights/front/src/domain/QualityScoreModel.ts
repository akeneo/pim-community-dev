export interface QualityScoreModel {
  [channel: string]: {
    [locale: string]: QualityScoreValue | null | 'N/A';
  };
}

export const allScoreValues = ['A', 'B', 'C', 'D', 'E'] as const;

export type QualityScoreValue = typeof allScoreValues[number];
