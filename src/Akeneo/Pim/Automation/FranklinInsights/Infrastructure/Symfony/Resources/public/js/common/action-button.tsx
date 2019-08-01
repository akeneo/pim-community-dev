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

interface Props {
  className?: string;
  label: string;
  count?: number;
  onClick: () => void;
}

export class ActionButton extends Component<Props> {
  get className() {
    let className = 'AknButton AknButton--action';
    if (this.props.className) {
      className += ' ' + this.props.className;
    }
    if (this.disabled) {
      className += ' AknButton--disabled';
    }

    return className;
  }

  get disabled() {
    return 0 === this.props.count;
  }

  public render() {
    return (
      <button onClick={this.props.onClick} className={this.className} disabled={this.disabled}>
        {this.props.label}
        {undefined !== this.props.count && <span className='AknButton--withSuffix'>{this.props.count}</span>}
      </button>
    );
  }
}

export default ActionButton;
