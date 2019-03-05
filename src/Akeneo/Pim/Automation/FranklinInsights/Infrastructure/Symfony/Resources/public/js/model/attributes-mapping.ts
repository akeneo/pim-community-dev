/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @author Paul Chasle <paul.chasle@akeneo.com>
 */
export default interface AttributesMapping {
  [franklinAttribute: string]: {
    franklinAttribute: {
      label: string,
      type: string,
      summary: string[],
    },
    attribute: string,
    status: number,
  };
}
