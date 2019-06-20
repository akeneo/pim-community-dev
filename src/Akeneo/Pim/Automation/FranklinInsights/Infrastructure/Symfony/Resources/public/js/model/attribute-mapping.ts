/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import AttributeMappingStatus from './attribute-mapping-status';

export default interface AttributeMapping {
  franklinAttribute: {
    label: string;
    type: string;
    summary: string[];
  };
  attribute: string | null;
  status: AttributeMappingStatus;
};
