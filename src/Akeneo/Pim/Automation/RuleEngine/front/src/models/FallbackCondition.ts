export type FallbackCondition = {
  type: 'FallbackCondition',
  json: any;
}

export const createFallbackCondition = (json: any) : FallbackCondition => {
  return {
    type: 'FallbackCondition',
    json: json
  };
};
