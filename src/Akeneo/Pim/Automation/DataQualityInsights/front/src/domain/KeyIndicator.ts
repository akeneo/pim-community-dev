export type Counts = {
  totalGood: number;
  totalToImprove: number;
  extraData?: KeyIndicatorExtraData;
};

export const makeCounts = (): Counts => ({
  totalGood: 0,
  totalToImprove: 0,
});

export type CountsByProductType = {
  [entitityKind in 'products' | 'product_models']: Counts;
};

export const makeCountsByProductType = (): CountsByProductType => ({
  products: makeCounts(),
  product_models: makeCounts(),
});

export const areAllCountsZero = (c: CountsByProductType) =>
  c.products.totalGood === 0 &&
  c.products.totalToImprove === 0 &&
  c.product_models.totalGood === 0 &&
  c.product_models.totalToImprove === 0;

export type KeyIndicatorMap = {
  [keyIndicatorCode: string]: CountsByProductType;
};

type Tip = {
  message: string;
  link?: string;
};

type KeyIndicatorTips = {
  [step: string]: Tip[];
};

type KeyIndicatorsTips = {
  [keyIndicatorCode: string]: KeyIndicatorTips;
};

export type KeyIndicatorExtraData = {
  impactedFamilies: string[];
};
