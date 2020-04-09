export type FallbackAction = {
  json: any;
}

export const createFallbackAction = (json: any): FallbackAction => {
  return {
    json: json
  }
};
