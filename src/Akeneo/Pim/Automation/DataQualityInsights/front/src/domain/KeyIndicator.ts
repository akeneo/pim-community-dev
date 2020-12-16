type KeyIndicator = {
  ratioGood: number;
  totalToImprove: number;
  extraData?: KeyIndicatorExtraData;
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

type KeyIndicatorExtraData = {
  impactedFamilies: string[];
};

export {KeyIndicator, keyIndicatorMap, Tip, KeyIndicatorTips, KeyIndicatorsTips, KeyIndicatorExtraData};
