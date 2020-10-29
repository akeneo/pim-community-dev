type FamilyCode = string;

type Family = {
  code: FamilyCode;
  labels: {
    [locale: string]: string;
  };
};

export {Family, FamilyCode};
