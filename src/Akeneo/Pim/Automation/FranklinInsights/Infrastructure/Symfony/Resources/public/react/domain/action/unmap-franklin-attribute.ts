/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export const UNMAP_FRANKLIN_ATTRIBUTE = 'UNMAP_FRANKLIN_ATTRIBUTE';

export interface UnmapFranklinAttributeAction {
  type: typeof UNMAP_FRANKLIN_ATTRIBUTE;
  familyCode: string;
  franklinAttributeCode: string;
}
