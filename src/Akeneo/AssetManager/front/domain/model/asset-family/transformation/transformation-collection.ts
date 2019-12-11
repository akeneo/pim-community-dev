type TransformationCollection = string;

export default TransformationCollection;

export const denormalizeAssetFamilyTransformations = (transformations: any): TransformationCollection => {
  return JSON.stringify(transformations, null, 4);
};
