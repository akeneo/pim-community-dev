/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as React from 'react';
import {PropsWithChildren} from 'react';

export const SecondaryActionsDropdown = ({children}: PropsWithChildren<{}>) => (
  <div className='AknDropdown'>
    <div className='AknSecondaryActions-button' style={{display: 'inline-block'}} data-toggle='dropdown'></div>
    <div className='AknDropdown-menu'>{children}</div>
  </div>
);
