type FamilyVariantCode = string;

type FamilyVariant = {
  code: FamilyVariantCode;
  labels: {
    [locale: string]: string;
  };
};

export { FamilyVariant, FamilyVariantCode };
