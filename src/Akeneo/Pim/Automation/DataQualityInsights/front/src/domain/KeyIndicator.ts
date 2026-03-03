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
  [entitityKind in 'products' | 'product_models']?: Counts;
};

export const makeCountsByProductType = (): CountsByProductType => ({
  products: makeCounts(),
  product_models: makeCounts(),
});

export const areCountsZero = (counts?: Counts): boolean =>
  !!counts && counts.totalGood === 0 && counts.totalToImprove === 0;

export const isCountsByProductType = (c: CountsByProductType | Counts): c is CountsByProductType => {
  return c.hasOwnProperty('products') || c.hasOwnProperty('product_models');
};

export const areAllCountsZero = (c: CountsByProductType | Counts): boolean => {
  if (isCountsByProductType(c)) {
    return areCountsZero(c.products) && areCountsZero(c.product_models);
  }
  return areCountsZero(c);
};

export const keyIndicatorProducts = ['has_image', 'good_enrichment', 'values_perfect_spelling'] as const;
export const keyIndicatorAttributes = ['attributes_perfect_spelling'] as const;
export type KeyIndicatorProducts = typeof keyIndicatorProducts[number];
export type KeyIndicatorAttributes = typeof keyIndicatorAttributes[number];

export function isKeyIndicatorProducts(code: string): code is KeyIndicatorProducts {
  return (keyIndicatorProducts as unknown as string[]).includes(code);
}

export type KeyIndicatorMap = {
  [code in KeyIndicatorProducts]?: CountsByProductType;
} & {
  [code in KeyIndicatorAttributes]?: Counts;
};

export type Tip = {
  message: string;
  link?: string;
};

export type KeyIndicatorTips = {
  [step: string]: Tip[];
};

export type KeyIndicatorsTips = {
  [keyIndicatorCode in string]: KeyIndicatorTips;
};

export type KeyIndicatorExtraData = {
  impactedFamilies: string[];
};
