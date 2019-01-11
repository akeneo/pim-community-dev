/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Simple escape for HTML codes module.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
export class EscapeHtml {
  /**
   * Simple escape for HTML codes
   *
   * @param {string } source
   * @return { string }
   */
  public static escapeHtml(source: string) {
    return String(source).replace(/[&<>"'\/]/g, (s) => EscapeHtml.entityMap[s]);
  }

  private static entityMap: { [key: string]: string } = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    '\'': '&#39;',
    '/': '&#x2F;',
  };
}
