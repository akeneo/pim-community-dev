import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import Money from '../../static/illustrations/Money.svg';

const MoneyIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256" {...props}>
    {title && <title>{title}</title>}
    <image href={Money} />
  </svg>
);

export {MoneyIllustration};
