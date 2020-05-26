import React from 'react';
import { CalculateActionLine } from '../../pages/EditRules/components/actions/CalculateActionLine';
import { ActionLineProps } from '../../pages/EditRules/components/actions/ActionLineProps';
import { ProductField } from './ProductField';
import { Operand, denormalizeOperand } from './Calculate/Operand';
import { denormalizeOperation, Operation } from './Calculate/Operation';

export type CalculateAction = {
  module: React.FC<{ action: CalculateAction } & ActionLineProps>;
  destination: ProductField;
  source: Operand;
  operation_list: Operation[];
  // TODO: uncomment when it will be ready
  // round_precision: number | null;
};

export const denormalizeCalculateAction = (
  json: any
): CalculateAction | null => {
  if (json.type !== 'calculate') {
    return null;
  }

  try {
    return {
      module: CalculateActionLine,
      destination: json.destination,
      source: denormalizeOperand(json.source),
      operation_list: json.operation_list.map((operation: any) =>
        denormalizeOperation(operation)
      ),
      // TODO: uncomment when it will be ready
      // round_precision: json.round_precision || null,
    };
  } catch (e) {
    console.error(e);

    return null;
  }
};
