import React from 'react';
import styled from 'styled-components';
import {Status} from '../../../rules.constants';
import {useTranslate} from '../../../dependenciesTools/hooks';

type Props = {
  count: number;
  status: Status;
};

const CountPending = styled.span`
  opacity: 0.5;
`;

const ProductsCount: React.FC<Props> = ({status, count}) => {
  const translate = useTranslate();

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
          {translate(
            'pimee_catalog_rule.form.edit.products_count.complete',
            {
              count,
            },
            count
          )}
        </span>
      )}
    </>
  );
};

export {ProductsCount};
