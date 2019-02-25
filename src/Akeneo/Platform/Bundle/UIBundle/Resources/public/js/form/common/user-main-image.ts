const MainImage = require('pim/form/common/main-image');
const UserContext = require('pim/user-context');
const MediaUrlGenerator = require('pim/media-url-generator');

/**
 * Display main image with user avatar
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserMainImage extends MainImage {
  /**
   * @{inheritdoc}
   */
  getPath() {
    const filePath = UserContext.get('avatar').filePath;
    if (null === filePath || undefined === filePath) {
      return 'bundles/pimui/images/info-user.png';
    }

    return MediaUrlGenerator.getMediaShowUrl(UserContext.get('avatar').filePath, 'thumbnail_small');
  }
}

export = UserMainImage
