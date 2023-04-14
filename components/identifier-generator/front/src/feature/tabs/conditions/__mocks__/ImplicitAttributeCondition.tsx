import React from 'react';
import {ChannelCode, LocaleCode} from '@akeneo-pim-community/shared';

type Props = {
  attributeCode?: string;
  locale?: LocaleCode | null;
  scope?: ChannelCode | null;
};

const ImplicitAttributeCondition: React.FC<Props> = ({attributeCode, scope, locale}) => (
  <>
    <p>ImplicitAttributeConditionMock</p>
    <p>Implicit attribute code: {attributeCode}</p>
    {scope && (
      <p>
        <span>Implicit attribute scope:</span> <span>{scope}</span>
      </p>
    )}
    {locale && (
      <p>
        <span>Implicit attribute locale:</span> <span>{locale}</span>
      </p>
    )}
  </>
);

export {ImplicitAttributeCondition};
