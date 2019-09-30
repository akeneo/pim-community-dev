/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as React from 'react';

export const UserContext = React.createContext<{catalogLocale: string; uiLocale: string}>({
  catalogLocale: 'en_US',
  uiLocale: 'en_US'
});
