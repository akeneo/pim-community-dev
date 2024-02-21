export default interface Rate {
  value: number | null;
  rank: string | null;
}

export const MAX_RATE = 100;

export const RANK_1 = 'A';
export const RANK_2 = 'B';
export const RANK_3 = 'C';
export const RANK_4 = 'D';
export const RANK_5 = 'E';

export const Ranks: any = {
  rank_1: RANK_1,
  rank_2: RANK_2,
  rank_3: RANK_3,
  rank_4: RANK_4,
  rank_5: RANK_5,
};

export const RANK_1_COLOR = '#528f5c';
export const RANK_2_COLOR = '#67b373';
export const RANK_3_COLOR = '#f9b53f';
export const RANK_4_COLOR = '#d4604f';
export const RANK_5_COLOR = '#a94c3f';
export const NO_RATE_COLOR = '#d9dde2';
