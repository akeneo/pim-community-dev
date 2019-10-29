/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export const SELECT_FRANKLIN_ATTRIBUTE = 'SELECT_FRANKLIN_ATTRIBUTE';

export interface SelectFranklinAttributeAction {
  type: typeof SELECT_FRANKLIN_ATTRIBUTE;
  franklinAttributeCode: string;
}

export function selectFranklinAttribute(franklinAttributeCode: string): SelectFranklinAttributeAction {
  return {
    type: SELECT_FRANKLIN_ATTRIBUTE,
    franklinAttributeCode
  };
}

export const UNSELECT_FRANKLIN_ATTRIBUTE = 'UNSELECT_FRANKLIN_ATTRIBUTE';

export interface UnselectFranklinAttributeAction {
  type: typeof UNSELECT_FRANKLIN_ATTRIBUTE;
  franklinAttributeCode: string;
}

export function unselectFranklinAttribute(franklinAttributeCode: string): UnselectFranklinAttributeAction {
  return {
    type: UNSELECT_FRANKLIN_ATTRIBUTE,
    franklinAttributeCode
  };
}

export const UNSELECT_ALL_FRANKLIN_ATTRIBUTES = 'UNSELECT_ALL_FRANKLIN_ATTRIBUTES';

export interface UnselectAllFranklinAttributesAction {
  type: typeof UNSELECT_ALL_FRANKLIN_ATTRIBUTES;
}

export function unselectAllFranklinAttributes(): UnselectAllFranklinAttributesAction {
  return {
    type: UNSELECT_ALL_FRANKLIN_ATTRIBUTES
  };
}

export const SELECT_ALL_FRANKLIN_ATTRIBUTES = 'SELECT_ALL_FRANKLIN_ATTRIBUTES';

export interface SelectAllFranklinAttributesAction {
  type: typeof SELECT_ALL_FRANKLIN_ATTRIBUTES;
  franklinAttributeCodes: string[];
}

export function selectAllFranklinAttributes(franklinAttributeCodes: string[]): SelectAllFranklinAttributesAction {
  return {
    type: SELECT_ALL_FRANKLIN_ATTRIBUTES,
    franklinAttributeCodes
  };
}

export type FranklinAttributeSelectionActions =
  | SelectFranklinAttributeAction
  | SelectAllFranklinAttributesAction
  | UnselectFranklinAttributeAction
  | UnselectAllFranklinAttributesAction;
