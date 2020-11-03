import React from 'react';
import styled from 'styled-components';
import {Translate} from '../../dependenciesTools';

const ArrowImg = styled.img`
  opacity: 0.5;
`;

type Props = {opened: boolean; translate: Translate};

const TreeArrow: React.FC<Props> = ({opened, translate}) => {
  if (!opened) {
    return (
      <ArrowImg
        src='/bundles/pimui/images/jstree/icon-right.svg'
        alt={translate('pimee_catalog_rule.form.category.arrow.close')}
      />
    );
  }
  return (
    <ArrowImg
      src='bundles/pimui/images/jstree/icon-down.svg'
      alt={translate('pimee_catalog_rule.form.category.arrow.open')}
    />
  );
};

export {TreeArrow};
