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

interface Props {
  onClick: () => void;
  count?: number;
  disabled?: boolean;
  classNames?: string[];
}

export const Button = ({
  children,
  onClick,
  count,
  disabled = 0 === count,
  classNames = []
}: PropsWithChildren<Props>) => {
  classNames.push('AknButton');
  if (disabled) {
    classNames.push('AknButton--disabled');
  }

  return (
    <button onClick={onClick} className={classNames.join(' ')} disabled={disabled}>
      {children}
      {undefined !== count && <span className='AknButton--withSuffix'>{count}</span>}
    </button>
  );
};

export const ActionButton = ({classNames = [], ...props}: PropsWithChildren<Props>) => {
  classNames.push('AknButton--action');

  return <Button {...props} classNames={classNames}></Button>;
};

export const GhostButton = ({classNames = [], ...props}: PropsWithChildren<Props>) => {
  classNames.push('AknButton--ghost');

  return <Button {...props} classNames={classNames}></Button>;
};
