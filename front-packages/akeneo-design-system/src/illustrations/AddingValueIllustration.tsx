import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import AddingValue from '../../static/illustrations/AddingValue.svg';

const AddingValueIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256" {...props}>
    {title && <title>{title}</title>}
    <image href={AddingValue} />
  </svg>
);

export {AddingValueIllustration};
