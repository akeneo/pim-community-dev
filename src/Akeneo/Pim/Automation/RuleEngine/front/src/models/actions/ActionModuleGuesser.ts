import React from 'react';
import { ActionLineProps } from '../../pages/EditRules/components/actions/ActionLineProps';
import { Action } from '../Action';
import {
  getAddActionModule,
  getAddAttributeValueActionModule,
  getAddCategoriesModule,
  getAddGroupsActionModule,
  getCalculateActionModule,
  getClearAssociationsActionModule,
  getClearAttributeActionModule,
  getClearCategoriesActionModule,
  getClearGroupsActionModule,
  getConcatenateActionModule,
  getCopyActionModule,
  getRemoveAttributeValueActionModule,
  getRemoveCategoriesModule,
  getRemoveGroupsActionModule,
  getSetActionModule,
  getSetCategoriesModule,
  getSetFamilyActionModule,
  getSetGroupsActionModule,
  getSetStatusActionModule,
} from './';
import { FallbackActionLine } from '../../pages/EditRules/components/actions/FallbackActionLine';
import { Router } from '../../dependenciesTools';

export type ActionModuleGuesser = (
  json: any,
  router: Router
) => Promise<React.FC<ActionLineProps & { action: Action }> | null>;

const getActionModule: (
  json: any,
  router: Router
) => Promise<React.FC<ActionLineProps & { action: Action }>> = async (
  json,
  router
) => {
  const getActionModuleFunctions: ActionModuleGuesser[] = [
    getAddCategoriesModule,
    getAddGroupsActionModule,
    getClearAssociationsActionModule,
    getClearCategoriesActionModule,
    getClearGroupsActionModule,
    getRemoveCategoriesModule,
    getRemoveGroupsActionModule,
    getSetCategoriesModule,
    getSetFamilyActionModule,
    getSetGroupsActionModule,
    getSetStatusActionModule,
    // Attribute values: they should be tested after the system field actions since their 'field' is dynamic
    getAddAttributeValueActionModule,
    getClearAttributeActionModule,
    getRemoveAttributeValueActionModule,
    // Fallbacks
    getAddActionModule,
    getCalculateActionModule,
    getConcatenateActionModule,
    getCopyActionModule,
    getSetActionModule,
  ];

  for (let i = 0; i < getActionModuleFunctions.length; i++) {
    const getModuleFunction = getActionModuleFunctions[i];
    const module = await getModuleFunction(json, router);
    if (module !== null) {
      return module;
    }
  }

  return FallbackActionLine;
};

export { getActionModule };
