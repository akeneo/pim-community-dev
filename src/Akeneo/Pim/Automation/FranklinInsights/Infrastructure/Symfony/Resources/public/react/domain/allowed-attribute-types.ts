/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {AttributeType} from './model/attribute-type.enum';

export const ALLOWED_ATTRIBUTE_TYPES: string[] = [
  AttributeType.BOOLEAN,
  AttributeType.METRIC,
  AttributeType.MULTISELECT,
  AttributeType.NUMBER,
  AttributeType.SIMPLESELECT,
  AttributeType.TEXT,
  AttributeType.TEXTAREA
];
