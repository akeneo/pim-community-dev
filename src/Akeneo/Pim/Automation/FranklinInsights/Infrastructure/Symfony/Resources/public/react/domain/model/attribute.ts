/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {AttributeType} from './attribute-type.enum';

export interface Attribute {
  code: string;
  type: AttributeType;
  labels: {[locale: string]: string};
  group: string;
}
