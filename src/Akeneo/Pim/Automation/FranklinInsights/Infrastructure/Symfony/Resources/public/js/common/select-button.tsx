/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as React from 'react';
import {Component} from 'react';

export enum SelectionState {
  Selected,
  Partial,
  Deselected
}

interface Props {
  selectionState?: SelectionState;
  onChange: (state: SelectionState) => void;
}

export class SelectButton extends Component<Props> {
  get className() {
    let className = 'AknSelectButton';

    if (this.props.selectionState === SelectionState.Selected) {
      className += ' AknSelectButton--selected';
    }

    if (this.props.selectionState === SelectionState.Partial) {
      className += ' AknSelectButton--partial';
    }

    return className;
  }

  public render() {
    return (
      <div
        className={this.className}
        onClick={() => this.props.onChange(this.getNextState(this.props.selectionState))}
      />
    );
  }

  private getNextState(selectionState?: SelectionState): SelectionState {
    if (selectionState === SelectionState.Deselected) {
      return SelectionState.Selected;
    }

    if (selectionState === SelectionState.Partial) {
      return SelectionState.Selected;
    }

    return SelectionState.Deselected;
  }
}

export default SelectButton;
