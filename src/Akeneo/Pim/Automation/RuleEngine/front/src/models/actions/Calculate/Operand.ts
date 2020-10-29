export type FieldOperand = {
  field: string;
  scope?: string | null;
  locale?: string | null;
  currency?: string | null;
};

export type ConstantOperand = {
  value: number;
};

export type Operand = FieldOperand | ConstantOperand;

export const denormalizeOperand = (data: any): Operand => {
  if (!data.field && !data.value) {
    throw 'Operand has neither field nor value.';
  }
  if (data.field && data.value) {
    throw 'Operand cannot have field and value.';
  }

  if (data.field) {
    return {
      scope: null,
      locale: null,
      ...data,
    };
  }

  return {value: data.value};
};
