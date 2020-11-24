export type ScoreDistribution = {
  [rank: string]: number;
};

export type ScoreDistributionByDate = {
  [date: string]: ScoreDistribution;
};

// ScoreDistribution model used with Chart
export type ScoreDistributionChartDatasetEntry = {
  x: string;
  y: number;
};

export type ScoreDistributionChartDataset = {
  [rank: string]: ScoreDistributionChartDatasetEntry[];
};
