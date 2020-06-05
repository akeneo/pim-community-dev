import { ConcatenateActionLine } from '../../pages/EditRules/components/actions/ConcatenateActionLine';
import { ProductField } from './ProductField';
import { ActionModuleGuesser } from "../Action";

export type ConcatenateAction = {
  type: 'concatenate';
  from: ProductField[];
  to: ProductField;
};

export const getConcatenateActionModule: ActionModuleGuesser = (json) => {
  if (json.type !== 'concatenate') {
    return Promise.resolve(null);
  }

  return Promise.resolve(ConcatenateActionLine);
};
