import { RuleDefinition } from './RuleDefinition';
import { Action, ActionModuleGuesser } from './Action';
import { Router } from '../dependenciesTools';
import { getAttributesByIdentifiers } from '../repositories/AttributeRepository';
import {
  getAddActionModule,
  getCalculateActionModule,
  getClearActionModule,
  getConcatenateActionModule,
  getCopyActionModule,
  getRemoveActionModule,
  getSetActionModule,
  getSetFamilyActionModule,
} from './actions';
import {
  Condition,
  ConditionModuleGuesser,
  getFamilyConditionModule,
  getMultiOptionsAttributeConditionModule,
  getCategoryConditionModule,
  getPimConditionModule,
  getTextAttributeConditionModule,
} from './conditions';
import React from 'react';
import { ConditionLineProps } from '../pages/EditRules/components/conditions/ConditionLineProps';
import { FallbackConditionLine } from '../pages/EditRules/components/conditions/FallbackConditionLine';
import { ActionLineProps } from '../pages/EditRules/components/actions/ActionLineProps';
import { FallbackActionLine } from '../pages/EditRules/components/actions/FallbackActionLine';

const getActionModule: ((
  json: any,
) => Promise<React.FC<ActionLineProps>>) = async (json) => {
  const getActionModuleFunctions: ActionModuleGuesser[] = [
    getSetFamilyActionModule,
    getAddActionModule,
    getCalculateActionModule,
    getClearActionModule,
    getConcatenateActionModule,
    getCopyActionModule,
    getRemoveActionModule,
    getSetActionModule,
  ];

  for (let i = 0; i < getActionModuleFunctions.length; i++) {
    const getModuleFunction = getActionModuleFunctions[i];
    const module = await getModuleFunction(json);
    if (module !== null) {
      return module;
    }
  }

  return FallbackActionLine;
};

const getConditionModule: (
  json: any,
  router: Router
) => Promise<React.FC<ConditionLineProps & { condition: Condition }>> = async (
  json,
  router
) => {
  const getConditionModuleFunctions: ConditionModuleGuesser[] = [
    getFamilyConditionModule,
    getCategoryConditionModule,
    getTextAttributeConditionModule,
    getMultiOptionsAttributeConditionModule,
    getPimConditionModule,
  ];

  for (let i = 0; i < getConditionModuleFunctions.length; i++) {
    const getModuleFunction = getConditionModuleFunctions[i];
    const module = await getModuleFunction(json, router);
    if (module !== null) {
      return module;
    }
  }

  return FallbackConditionLine;
};

const extractFieldIdentifiers = (json: any): string[] => {
  const indexedFieldIdentifiers: { [identifier: string]: boolean } = {};

  const conditions = json.content?.conditions || [];
  if (Array.isArray(conditions)) {
    conditions.forEach((condition: any) => {
      if ('string' === typeof condition.field) {
        indexedFieldIdentifiers[condition.field] = true;
      }
    });
  }

  const actions = json.content?.actions || [];
  if (Array.isArray(actions)) {
    actions.forEach((action: any) => {
      if ('string' === typeof action.field) {
        indexedFieldIdentifiers[action.field] = true;
      }
    });
  }

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
  if (
    typeof json.code !== 'string' ||
    (typeof json.labels !== 'undefined' && typeof json.labels !== 'object') ||
    (typeof json.priority !== 'undefined' && typeof json.priority !== 'number')
  ) {
    throw new Error('Unable to parse rule definition ' + JSON.stringify(json));
  }
  const code = json.code;
  const labels = json.labels || {};
  const priority = json.priority || 0;

  await prepareCacheAttributes(json, router);

  return {
    code: code,
    labels: labels,
    priority: priority,
    conditions: json.content?.conditions || [],
    actions: json.content?.actions || [],
  };
};

export { getConditionModule, getActionModule };
