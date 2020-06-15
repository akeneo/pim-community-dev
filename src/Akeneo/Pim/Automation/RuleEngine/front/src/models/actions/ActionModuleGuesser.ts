import React from 'react';
import { ActionLineProps } from '../../pages/EditRules/components/actions/ActionLineProps';
import { Action } from '../Action';
import { getSetFamilyActionModule } from './SetFamilyAction';
import { getAddActionModule } from './AddAction';
import { getCalculateActionModule } from './CalculateAction';
import { getClearActionModule } from './ClearAction';
import { getConcatenateActionModule } from './ConcatenateAction';
import { getCopyActionModule } from './CopyAction';
import { getRemoveActionModule } from './RemoveAction';
import { getSetActionModule } from './SetAction';
import { FallbackActionLine } from '../../pages/EditRules/components/actions/FallbackActionLine';
import { Router } from '../../dependenciesTools';
import { getClearAttributeActionModule } from './ClearAttributeAction';
import { getAddCategoriesModule } from './AddCategoriesAction';

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
    getSetFamilyActionModule,
    getClearAttributeActionModule,
    getAddCategoriesModule,
    // Fallbacks
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
    const module = await getModuleFunction(json, router);
    if (module !== null) {
      return module;
    }
  }

  return FallbackActionLine;
};

export { getActionModule };
