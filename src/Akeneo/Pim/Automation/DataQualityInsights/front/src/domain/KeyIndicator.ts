type KeyIndicator = {
  ratioGood: number;
  totalToImprove: number;
};

type keyIndicatorMap = {
  [keyIndicator: string]: KeyIndicator;
};

type Tip = {
  message: string;
  link?: string;
};

type KeyIndicatorTips = {
  [step: string]: Tip[];
};

type KeyIndicatorsTips = {
  [keyIndicatorName: string]: KeyIndicatorTips;
};

export {KeyIndicator, keyIndicatorMap, Tip, KeyIndicatorTips, KeyIndicatorsTips};
