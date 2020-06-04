import { RuleDefinition } from './RuleDefinition';
import { denormalizeFallbackAction } from './FallbackAction';
import { Action, ActionDenormalizer } from './Action';
import { Router } from '../dependenciesTools';
import { getAttributesByIdentifiers } from '../repositories/AttributeRepository';
import {
  denormalizeAddAction,
  denormalizeCalculateAction,
  denormalizeClearAction,
  denormalizeClearAttributeAction,
  denormalizeConcatenateAction,
  denormalizeCopyAction,
  denormalizeRemoveAction,
  denormalizeSetAction,
  denormalizeSetFamilyAction,
} from './actions';
import {
  getFamilyConditionModule,
  getMultiOptionsAttributeConditionModule,
  getCategoryConditionModule,
  getPimConditionModule,
  getTextAttributeConditionModule
} from './conditions';
import React from "react";
import { ConditionLineProps } from "../pages/EditRules/components/conditions/ConditionLineProps";
import { FallbackConditionLine } from "../pages/EditRules/components/conditions/FallbackConditionLine";

async function denormalizeAction(
  jsonAction: any,
  router: Router
): Promise<Action> {
  const denormalizers: ActionDenormalizer[] = [
    // Order is important: the first denormalizer that returns an Action is used.
    denormalizeSetFamilyAction,
    denormalizeClearAttributeAction,
    // Fallback actions
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
    const action = await denormalizer(jsonAction, router);
    if (action !== null) {
      return action;
    }
  }

  return denormalizeFallbackAction(jsonAction);
}

const getConditionModule: ((
  json: any,
  router: Router
) => Promise<React.FC<ConditionLineProps>>) = async (json, router) => {
  const getModuleFunctions: ((json: any, router: Router) => Promise<React.FC<ConditionLineProps> | null>)[] = [
    getFamilyConditionModule,
    getCategoryConditionModule,
    getTextAttributeConditionModule,
    getMultiOptionsAttributeConditionModule,
    getPimConditionModule,
  ];

  for (let i = 0; i < getModuleFunctions.length; i++) {
    const getModuleFunction = getModuleFunctions[i];
    const condition = await getModuleFunction(json, router);
    if (condition !== null) {
      return condition;
    }
  }

  return FallbackConditionLine;
}

/*
export async function denormalizeCondition(
  jsonCondition: any,
  router: Router
): Promise<Condition> {
  const denormalizers: ConditionDenormalizer[] = [
    denormalizeFamilyCondition,
    denormalizeTextAttributeCondition,
    denormalizeMultiOptionsAttributeCondition,
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
 */

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

  await prepareCacheAttributes(json, router);

  if (Array.isArray(json.content.actions)) {
    actions = await Promise.all(
      json.content.actions.map(async (jsonAction: any) => {
        return await denormalizeAction(jsonAction, router);
      })
    );
  }

  return {
    code: code,
    labels: labels,
    priority: priority,
    conditions: json.content?.conditions || [],
    actions: actions,
  };
};

export { getConditionModule };
