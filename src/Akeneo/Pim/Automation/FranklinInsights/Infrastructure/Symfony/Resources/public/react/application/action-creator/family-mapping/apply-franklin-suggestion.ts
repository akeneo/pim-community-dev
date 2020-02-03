/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {
  APPLY_FRANKLIN_SUGGESTION,
  ApplyFranklinSuggestionAction
} from '../../../domain/action/apply-franklin-suggestion';

export function applyFranklinSuggestion(
  familyCode: string,
  franklinAttributeCode: string,
  pimAttributeCode: string
): ApplyFranklinSuggestionAction {
  return {
    type: APPLY_FRANKLIN_SUGGESTION,
    familyCode,
    franklinAttributeCode,
    pimAttributeCode
  };
}
