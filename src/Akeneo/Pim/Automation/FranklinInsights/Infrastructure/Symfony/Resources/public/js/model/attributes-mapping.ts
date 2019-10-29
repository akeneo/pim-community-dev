/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {FamilyMappingStatus} from '../../react/domain/model/family-mapping-status.enum';

export interface AttributesMapping {
  familyCode?: string;
  familyMappingStatus: FamilyMappingStatus;
  hasUnsavedChanges: boolean;
  attributeCount: number;
  mappedAttributeCount: number;
}
