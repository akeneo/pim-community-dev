/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import AttributesMapping from './attributes-mapping';

/**
 * @author Paul Chasle <paul.chasle@akeneo.com>
 */
export default interface AttributesMappingForFamily {
  code: string;
  mapping: AttributesMapping;
}
