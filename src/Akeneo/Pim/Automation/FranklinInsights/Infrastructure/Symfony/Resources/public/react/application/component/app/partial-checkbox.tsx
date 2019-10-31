/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as React from 'react';

export enum CheckboxState {
  Checked,
  Partial,
  Unchecked
}

interface Props {
  checkboxState: CheckboxState;
  onChange: (state: CheckboxState) => void;
}

export const PartialCheckbox = ({checkboxState, onChange}: Props) => {
  const className = getClassName(checkboxState);

  return <div className={className} onClick={() => onChange(checkboxState)} />;
};

function getClassName(checkboxState: CheckboxState): string {
  let className = 'AknSelectButton';

  if (CheckboxState.Checked === checkboxState) {
    className += ' AknSelectButton--selected';
  }

  if (CheckboxState.Partial === checkboxState) {
    className += ' AknSelectButton--partial';
  }

  return className;
}
