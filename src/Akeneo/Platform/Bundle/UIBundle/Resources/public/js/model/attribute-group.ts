/**
 * Interface for the Attribute Group from the backend
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
export default interface NormalizedAttributeGroup {
  labels: { [locale: string]: string };
}
