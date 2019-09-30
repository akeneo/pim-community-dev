/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {
  UNSELECT_FRANKLIN_ATTRIBUTE,
  FranklinAttributeSelectionActions,
  SELECT_FRANKLIN_ATTRIBUTE,
  SELECT_ALL_FRANKLIN_ATTRIBUTES,
  UNSELECT_ALL_FRANKLIN_ATTRIBUTES,
  SelectFranklinAttributeAction,
  SelectAllFranklinAttributesAction,
  UnselectFranklinAttributeAction
} from '../../action/family-mapping/franklin-attribute-selection';
import {createReducer} from '../../../infrastructure/create-reducer';

export type FranklinAttributeSelectionState = string[];

const initialState: FranklinAttributeSelectionState = [];

const selectFranklinAttribute = (
  state: FranklinAttributeSelectionState,
  action: SelectFranklinAttributeAction
): FranklinAttributeSelectionState => [...state, action.franklinAttributeCode];

const selectAllFranklinAttribute = (
  _: FranklinAttributeSelectionState,
  action: SelectAllFranklinAttributesAction
): FranklinAttributeSelectionState => action.franklinAttributeCodes;

const unselectFranklinAttribute = (
  state: FranklinAttributeSelectionState,
  action: UnselectFranklinAttributeAction
): FranklinAttributeSelectionState =>
  state.filter(franklinAttributeCode => franklinAttributeCode !== action.franklinAttributeCode);

const unselectAllFranklinAttributes = (): FranklinAttributeSelectionState => [];

export default createReducer<FranklinAttributeSelectionState, FranklinAttributeSelectionActions>(initialState, {
  [SELECT_FRANKLIN_ATTRIBUTE]: selectFranklinAttribute,
  [SELECT_ALL_FRANKLIN_ATTRIBUTES]: selectAllFranklinAttribute,
  [UNSELECT_FRANKLIN_ATTRIBUTE]: unselectFranklinAttribute,
  [UNSELECT_ALL_FRANKLIN_ATTRIBUTES]: unselectAllFranklinAttributes
});
