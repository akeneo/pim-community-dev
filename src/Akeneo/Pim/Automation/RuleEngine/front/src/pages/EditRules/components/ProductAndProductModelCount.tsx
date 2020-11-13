import React from 'react';
import styled from 'styled-components';
import {Status} from '../../../rules.constants';
import {useTranslate} from '../../../dependenciesTools/hooks';

type Props = {
  productCount: number;
  productModelCount: number;
  status: Status;
};

const CountPending = styled.span`
  opacity: 0.5;
`;

const ProductAndProductModelCount: React.FC<Props> = ({
  status,
  productCount,
  productModelCount,
}) => {
  const translate = useTranslate();

  const productText = translate(
    'pimee_catalog_rule.form.edit.products_count.products',
    {
      count: productCount,
    },
    productCount
  );
  const productModelText = translate(
    'pimee_catalog_rule.form.edit.products_count.product_models',
    {
      count: productModelCount,
    },
    productModelCount
  );
  const productAndProductModelText = translate(
    'pimee_catalog_rule.form.edit.products_count.products_and_product_models',
    {
      products: productText,
      product_models: productModelText,
    }
  );

  return (
    <>
      {status === Status.ERROR && (
        <span className='AknSubsection-comment AknSubsection-comment--clickable'>
          {translate('pimee_catalog_rule.form.edit.products_count.error')}
        </span>
      )}
      {status === Status.PENDING && (
        <CountPending className='AknSubsection-comment AknSubsection-comment--clickable'>
          {translate('pimee_catalog_rule.form.edit.products_count.pending')}
        </CountPending>
      )}
      {status === Status.COMPLETE && (
        <span className='AknSubsection-comment AknSubsection-comment--clickable'>
          {productCount > 0
            ? productModelCount > 0
              ? productAndProductModelText
              : productText
            : productModelCount > 0
            ? productModelText
            : productAndProductModelText}
        </span>
      )}
    </>
  );
};

export {ProductAndProductModelCount};
