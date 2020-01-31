/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export const APPLY_FRANKLIN_SUGGESTION = 'APPLY_FRANKLIN_SUGGESTION';

export interface ApplyFranklinSuggestionAction {
  type: typeof APPLY_FRANKLIN_SUGGESTION;
  familyCode: string;
  franklinAttributeCode: string;
  pimAttributeCode: string;
}
