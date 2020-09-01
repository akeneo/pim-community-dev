export type Ranks = {
  [rank: string]: number;
}

export type AxisRates = {
  [date: string]: Ranks;
};

export type Dataset = {
  [axisName: string]: AxisRates;
};

export const formatBackendRanksToVictoryFormat = (dataset: Dataset, axisName: string): any => {
  if (Object.keys(dataset).length === 0) {
    return {};
  }

  let ranks: {[rank: string]: any[]} = {
    'rank_5': [],
    'rank_4': [],
    'rank_3': [],
    'rank_2': [],
    'rank_1': [],
    'rank_6': [],
  };

  Object.entries(dataset[axisName]).map(([date, ranksByDay]) => {
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
