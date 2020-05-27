import { RuleDefinition } from './RuleDefinition';
import { Condition, ConditionDenormalizer } from './Condition';
import { denormalizeFamilyCondition } from './FamilyCondition';
import { denormalizeFallbackCondition } from './FallbackCondition';
import { denormalizeFallbackAction, FallbackAction } from './FallbackAction';
import { Action } from './Action';
import { denormalizePimCondition } from './PimCondition';
import { denormalizeTextAttributeCondition } from './TextAttributeCondition';
import { Router } from '../dependenciesTools';
import { getAttributesByIdentifiers } from '../repositories/AttributeRepository';
import {
  denormalizeAddAction,
  denormalizeCalculateAction,
  denormalizeClearAction,
  denormalizeConcatenateAction,
  denormalizeCopyAction,
  denormalizeRemoveAction,
  denormalizeSetAction, denormalizeSetFamilyAction,
} from './actions';

function denormalizeAction(jsonAction: any): Action {
  const denormalizers: ((json: any) => Action | null)[] = [
    denormalizeSetFamilyAction,
    denormalizeAddAction,
    denormalizeCalculateAction,
    denormalizeClearAction,
    denormalizeConcatenateAction,
    denormalizeCopyAction,
    denormalizeRemoveAction,
    denormalizeSetAction,
  ];

  for (let i = 0; i < denormalizers.length; i++) {
    const denormalizer = denormalizers[i];
    const action = denormalizer(jsonAction);
    if (action !== null) {
      return action;
    }
  }

  return denormalizeFallbackAction(jsonAction);
}

async function denormalizeCondition(
  jsonCondition: any,
  router: Router
): Promise<Condition> {
  const denormalizers: ConditionDenormalizer[] = [
    denormalizeFamilyCondition,
    denormalizeTextAttributeCondition,
    denormalizePimCondition,
  ];

  for (let i = 0; i < denormalizers.length; i++) {
    const denormalize = denormalizers[i];
    const condition = await denormalize(jsonCondition, router);
    if (condition !== null) {
      return condition;
    }
  }

  return denormalizeFallbackCondition(jsonCondition);
}

const extractFieldIdentifiers = (json: any): string[] => {
  if (
    'undefined' === typeof json.content ||
    'undefined' === typeof json.content.conditions ||
    !Array.isArray(json.content.conditions)
  ) {
    return [];
  }

  const indexedFieldIdentifiers: { [identifier: string]: boolean } = {};
  json.content.conditions.forEach((condition: any) => {
    if ('string' === typeof condition.field) {
      indexedFieldIdentifiers[condition.field] = true;
    }
  });

  return Object.keys(indexedFieldIdentifiers);
};

const prepareCacheAttributes = async (
  json: any,
  router: Router
): Promise<void> => {
  const fieldIdentifiers = extractFieldIdentifiers(json);
  await getAttributesByIdentifiers(fieldIdentifiers, router);
};

export const denormalize = async function(
  json: any,
  router: Router
): Promise<RuleDefinition> {
  const code = json.code;
  const labels = json.labels;
  const priority = json.priority;
  let actions: FallbackAction[] = [];
  let conditions: Condition[] = [];

  await prepareCacheAttributes(json, router);

  if (Array.isArray(json.content.actions)) {
    actions = json.content.actions.map((jsonAction: any) => {
      return denormalizeAction(jsonAction);
    });
  }

  if (Array.isArray(json.content.conditions)) {
    conditions = (await Promise.all(
      json.content.conditions.map(async (jsonCondition: any) => {
        return await denormalizeCondition(jsonCondition, router);
      })
    )) as Condition[];
  }

  return {
    code: code,
    labels: labels,
    priority: priority,
    conditions: conditions,
    actions: actions,
  };
};
