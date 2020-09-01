import { CalculateActionLine } from '../../pages/EditRules/components/actions/CalculateActionLine';
import { ProductField } from './ProductField';
import { Operand, denormalizeOperand } from './Calculate/Operand';
import { denormalizeOperation, Operation } from './Calculate/Operation';
import { ActionModuleGuesser } from './ActionModuleGuesser';

export type CalculateAction = {
  type: 'calculate';
  destination: ProductField;
  source?: Operand;
  operation_list: Operation[];
  round_precision?: number | null;
};

export const getCalculateActionModule: ActionModuleGuesser = json => {
  if (json.type !== 'calculate') {
    return Promise.resolve(null);
  }

  // Value just created via the UI. Invalid, but this is the right module.
  if (typeof json.source === 'undefined' && json.operation_list.length === 0) {
    return Promise.resolve(CalculateActionLine);
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

export const createCalculateAction: () => CalculateAction = () => {
  return {
    type: 'calculate',
    destination: {
      field: '',
    },
    operation_list: [],
  };
};
