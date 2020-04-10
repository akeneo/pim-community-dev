export type FallbackCondition = {
  json: any;
}

export const createFallbackCondition = (json: any) : FallbackCondition => {
  return {
    json: json
  }
};
