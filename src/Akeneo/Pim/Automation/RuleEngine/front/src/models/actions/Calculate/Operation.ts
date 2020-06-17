import { denormalizeOperand, Operand } from './Operand';

export enum Operator {
  ADD = 'add',
  SUBSTRACT = 'substract',
  MULTIPLY = 'multiply',
  DIVIDE = 'divide',
}

export type Operation = {
  operator: Operator;
} & Operand;

export const denormalizeOperation = (data: any): Operation => {
  return {
    operator: data.operator,
    ...denormalizeOperand(data),
  };
};
