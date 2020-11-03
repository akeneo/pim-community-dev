/**
 * A PimCondition is a condition but not coded for now.
 * Its difference with the fallback is that it can be have its renderer.
 * Each native condition coming from the PIM has the same fields.
 */
import {PimConditionLine} from '../../pages/EditRules/components/conditions/PimConditionLine';
import {ConditionModuleGuesser} from './ConditionModuleGuesser';

type PimCondition = {
  field: string;
  operator: string;
  value: any | null;
  locale: string | null;
  scope: string | null;
};

export const getPimConditionModule: ConditionModuleGuesser = async json => {
  if (typeof json.field === 'string' && typeof json.operator === 'string') {
    return Promise.resolve(PimConditionLine);
  }

  return Promise.resolve(null);
};

export {PimCondition};
