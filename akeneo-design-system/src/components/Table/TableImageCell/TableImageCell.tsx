import React, {Ref} from 'react';
import styled from 'styled-components';
import {TableCell} from '../TableCell/TableCell';

const TableImageCellContainer = styled.img<{fit: 'cover' | 'contain'}>`
  background: white;
  position: relative;
  height: 44px;
  width: 44px;
  border: 1px solid #ccd1d8;
  object-fit: ${({fit}) => fit};
`;

const StackedLayerContainer = styled.div`
  position: absolute;
  border: 1px solid #ccd1d8;
  background: white;
  height: 44px;
  width: 44px;
  transform: translate(4px, -4px);

  :after {
    content: '';
    position: absolute;
    border: 1px solid #ccd1d8;
    background: white;
    height: 44px;
    width: 44px;
    top: 1px;
    right: 1px;
  }
`;

type TableImageCellProps = {
  /**
   * Define the image source
   */
  src: string;

  /**
   * Content of the alternative text
   */
  alt: string;

  /**
   * Should the image cover all the container or be contained in it.
   */
  fit?: 'cover' | 'contain';

  /**
   * Should the image is part of multiple images
   */
  isStacked?: boolean;
};

const TableImageCell = React.forwardRef<HTMLTableCellElement, TableImageCellProps>(
  (
    {alt, src, fit = 'cover', isStacked = false, ...rest}: TableImageCellProps,
    forwardedRef: Ref<HTMLTableCellElement>
  ) => {
    return (
      <TableCell ref={forwardedRef} {...rest}>
        {isStacked && <StackedLayerContainer />}
        <TableImageCellContainer fit={fit} src={src} alt={alt} />
      </TableCell>
    );
  }
);

export {TableImageCell};
