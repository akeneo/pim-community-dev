import {formatBackendRanksToVictoryFormat} from '@akeneo-pim-community/data-quality-insights/src/application/helper/Dashboard/FormatBackendRanksToVictoryFormat';

const dates = ['2020-01-30', '2020-01-31', '2020-02-01', '2020-02-02', '2020-02-03', '2020-02-04', '2020-02-05'];

describe('Dashboard convert backend ranks to Victory format', () => {
  test('An empty dataset should not return any ranks', () => {
    expect(formatBackendRanksToVictoryFormat({}, 'consistency')).toMatchObject({});
  });

  test('No axis rates should return data for only the rank 6', () => {
    const backendRanks = {
      consistency: {
        '2020-01-30': {},
        '2020-01-31': {},
        '2020-02-01': {},
        '2020-02-02': {},
        '2020-02-03': {},
        '2020-02-04': {},
        '2020-02-05': {},
      },
    };
    const rates0 = dates.map((date: string) => {
      return {x: date, y: 0};
    });
    const rates100 = dates.map((date: string) => {
      return {x: date, y: 100};
    });
    const expectedConsistencyRanks = {
      rank_5: rates0,
      rank_4: rates0,
      rank_3: rates0,
      rank_2: rates0,
      rank_1: rates0,
      rank_6: rates100,
    };
    const ranks = formatBackendRanksToVictoryFormat(backendRanks, 'consistency');
    expect(ranks).toMatchObject(expectedConsistencyRanks);
  });

  test('No ranks for 1 day is handled correctly', () => {
    const backendRanks = {
      consistency: {
        '2020-01-30': {
          rank_1: 10,
          rank_2: 20,
          rank_3: 30,
          rank_4: 15,
          rank_5: 25,
        },
        '2020-01-31': {
          rank_1: 10,
          rank_2: 20,
          rank_3: 30,
          rank_4: 15,
          rank_5: 25,
        },
        '2020-02-01': {
          rank_1: 10,
          rank_2: 20,
          rank_3: 30,
          rank_4: 15,
          rank_5: 25,
        },
        '2020-02-02': {
          rank_1: 10,
          rank_2: 20,
          rank_3: 30,
          rank_4: 15,
          rank_5: 25,
        },
        '2020-02-03': {
          rank_1: 10,
          rank_2: 20,
          rank_3: 30,
          rank_4: 15,
          rank_5: 25,
        },
        '2020-02-04': {},
        '2020-02-05': {
          rank_1: 10,
          rank_2: 20,
          rank_3: 30,
          rank_4: 15,
          rank_5: 25,
        },
      },
    };
    const expectedConsistencyRanks = {
      rank_5: [
        {x: '2020-01-30', y: 25},
        {x: '2020-01-31', y: 25},
        {x: '2020-02-01', y: 25},
        {x: '2020-02-02', y: 25},
        {x: '2020-02-03', y: 25},
        {x: '2020-02-04', y: 0},
        {x: '2020-02-05', y: 25},
      ],
      rank_4: [
        {x: '2020-01-30', y: 15},
        {x: '2020-01-31', y: 15},
        {x: '2020-02-01', y: 15},
        {x: '2020-02-02', y: 15},
        {x: '2020-02-03', y: 15},
        {x: '2020-02-04', y: 0},
        {x: '2020-02-05', y: 15},
      ],
      rank_3: [
        {x: '2020-01-30', y: 30},
        {x: '2020-01-31', y: 30},
        {x: '2020-02-01', y: 30},
        {x: '2020-02-02', y: 30},
        {x: '2020-02-03', y: 30},
        {x: '2020-02-04', y: 0},
        {x: '2020-02-05', y: 30},
      ],
      rank_2: [
        {x: '2020-01-30', y: 20},
        {x: '2020-01-31', y: 20},
        {x: '2020-02-01', y: 20},
        {x: '2020-02-02', y: 20},
        {x: '2020-02-03', y: 20},
        {x: '2020-02-04', y: 0},
        {x: '2020-02-05', y: 20},
      ],
      rank_1: [
        {x: '2020-01-30', y: 10},
        {x: '2020-01-31', y: 10},
        {x: '2020-02-01', y: 10},
        {x: '2020-02-02', y: 10},
        {x: '2020-02-03', y: 10},
        {x: '2020-02-04', y: 0},
        {x: '2020-02-05', y: 10},
      ],
      rank_6: [
        {x: '2020-01-30', y: 0},
        {x: '2020-01-31', y: 0},
        {x: '2020-02-01', y: 0},
        {x: '2020-02-02', y: 0},
        {x: '2020-02-03', y: 0},
        {x: '2020-02-04', y: 100},
        {x: '2020-02-05', y: 0},
      ],
    };
    const ranks = formatBackendRanksToVictoryFormat(backendRanks, 'consistency');
    expect(ranks).toMatchObject(expectedConsistencyRanks);
  });
});
