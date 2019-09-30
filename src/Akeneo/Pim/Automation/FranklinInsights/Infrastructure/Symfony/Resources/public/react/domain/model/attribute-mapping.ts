/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {AttributeMappingStatus} from './attribute-mapping-status.enum';
import {FranklinAttributeType} from './franklin-attribute-type.enum';

export interface AttributeMapping {
  franklinAttribute: {
    code: string;
    label: string;
    type: FranklinAttributeType;
    summary: string[];
  };
  attribute: string | null;
  status: AttributeMappingStatus;
  exactMatchAttributeFromOtherFamily: string | null;
  canCreateAttribute: boolean;
}
