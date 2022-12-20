import {Validator} from './Validator';
import {ALLOWED_CONDITION_NAMES, CONDITION_NAMES, Conditions} from '../models';
import {Violation} from './Violation';
import {validateEnabled} from './validateEnabled';

const validateConditions: Validator<Conditions | undefined> = (conditions, path) => {
  const violations: Violation[] = [];

  conditions?.forEach((condition, i) => {
    const subPath = `${path}[${i}]`;
    if (!ALLOWED_CONDITION_NAMES.includes(condition.type)) {
      violations.push({
        path: subPath,
        message: `The condition type "${
          condition.type
        }" is unknown. Please choose one of the following: ${ALLOWED_CONDITION_NAMES.join(', ')}`,
      });
    }

    if (condition.type === CONDITION_NAMES.ENABLED) {
      violations.push(...validateEnabled(condition, subPath));
    }
  });

  return violations;
};

export {validateConditions};
