export type AssociationTypeCode = string;

export type AssociationType = {
  code: AssociationTypeCode;
  is_quantified: boolean;
  is_two_way: boolean;
  labels: { [key: string]: string };
  meta: {
    id: number;
  } & {
    [key: string]: any;
  };
};
