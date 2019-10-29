/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export const MAP_FRANKLIN_ATTRIBUTE = 'MAP_FRANKLIN_ATTRIBUTE';

export interface MapFranklinAttributeAction {
  type: typeof MAP_FRANKLIN_ATTRIBUTE;
  familyCode: string;
  franklinAttributeCode: string;
  attributeCode: string;
}
