import {ScoreDistributionByDate, ScoreDistributionChartDataset} from '../../../domain';

export const formatBackendRanksToVictoryFormat = (dataset: ScoreDistributionByDate): ScoreDistributionChartDataset => {
  if (Object.keys(dataset).length === 0) {
    return {};
  }

  let ranks: ScoreDistributionChartDataset = {
    rank_5: [],
    rank_4: [],
    rank_3: [],
    rank_2: [],
    rank_1: [],
    rank_6: [],
  };

  Object.entries(dataset).map(([date, ranksByDay]) => {
    if (Object.keys(ranksByDay).length === 0) {
      ranks['rank_5'].push({x: date, y: 0});
      ranks['rank_4'].push({x: date, y: 0});
      ranks['rank_3'].push({x: date, y: 0});
      ranks['rank_2'].push({x: date, y: 0});
      ranks['rank_1'].push({x: date, y: 0});
      ranks['rank_6'].push({x: date, y: 100});
    } else {
      Object.entries(ranksByDay).map(([rank, distribution]) => {
        ranks[rank].push({x: date, y: distribution});
      });
      ranks['rank_6'].push({x: date, y: 0});
    }
  });

  return ranks;
};
