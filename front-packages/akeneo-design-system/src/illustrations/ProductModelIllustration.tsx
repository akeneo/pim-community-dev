import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import ProductModel from '../../static/illustrations/ProductModel.svg';

const ProductModelIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256" {...props}>
    {title && <title>{title}</title>}
    <image href={ProductModel} />
  </svg>
);

export {ProductModelIllustration};
