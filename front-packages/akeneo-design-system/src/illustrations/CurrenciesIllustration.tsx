import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import Currencies from '../../static/illustrations/Currencies.svg';

const CurrenciesIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256" {...props}>
    {title && <title>{title}</title>}
    <image href={Currencies} />
  </svg>
);

export {CurrenciesIllustration};
