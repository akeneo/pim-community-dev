import { CalculateActionLine } from '../../pages/EditRules/components/actions/CalculateActionLine';
import { ProductField } from './ProductField';
import { Operand, denormalizeOperand } from './Calculate/Operand';
import { denormalizeOperation, Operation } from './Calculate/Operation';
import { ActionModuleGuesser } from './ActionModuleGuesser';

export type CalculateAction = {
  type: 'calculate';
  destination: ProductField;
  source: Operand;
  operation_list: Operation[];
  round_precision?: number | null;
};

export const getCalculateActionModule: ActionModuleGuesser = json => {
  if (json.type !== 'calculate') {
    return Promise.resolve(null);
  }

  try {
    denormalizeOperand(json.source);
    json.operation_list.map((operation: any) =>
      denormalizeOperation(operation)
    );

    return Promise.resolve(CalculateActionLine);
  } catch (e) {
    return Promise.resolve(null);
  }
};
