/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {AttributesMapping} from './attributes-mapping';
import {FamilyMappingStatus} from './family-mapping-status.enum';

export interface FamilyMapping {
  code: string;
  mapping: AttributesMapping;
  status: FamilyMappingStatus;
}
