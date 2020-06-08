import React from 'react';
import { SetAction } from '../../../../models/actions';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { useValueInitialization } from '../../hooks/useValueInitialization';
import { FallbackField } from '../FallbackField';

type Props = {
  action: SetAction;
} & ActionLineProps;

const SetActionLine: React.FC<Props> = ({
  translate,
  lineNumber,
  action,
  handleDelete,
}) => {
  const values: any = {
    type: 'set',
    field: action.field,
    value: action.value,
  };
  if (action.locale) {
    values.locale = action.locale;
  }
  if (action.scope) {
    values.scope = action.scope;
  }
  useValueInitialization(`content.actions[${lineNumber}]`, values, {}, [
    action,
  ]);

  const displayNull = (value: any): string | null => {
    return null === value ? '' : null;
  };
  const displayPrice = (price: any): string | null => {
    if (
      Object.keys(price).includes('amount') &&
      Object.keys(price).includes('currency')
    ) {
      return `${price.amount} ${price.currency}`;
    }

    return null;
  };
  const displayMetric = (metric: any): string | null => {
    if (
      Object.keys(metric).includes('amount') &&
      Object.keys(metric).includes('unit')
    ) {
      return `${metric.amount} ${metric.unit}`;
    }

    return null;
  };

  const displaySingleValue = (value: any): string => {
    switch (typeof value) {
      case 'boolean':
        return value ? 'true' : 'false';
      case 'object':
        return (
          displayNull(value) ||
          displayPrice(value) ||
          displayMetric(value) ||
          JSON.stringify(value)
        );
      default:
        return value as string;
    }
  };

  const displayValue = (values: any): string => {
    if (Array.isArray(values)) {
      return values.map((value: any) => displaySingleValue(value)).join(', ');
    }

    return displaySingleValue(values);
  };

  return (
    <ActionTemplate
      translate={translate}
      title='Set Action'
      helper='This feature is under development. Please use the import to manage your rules.'
      legend='This feature is under development. Please use the import to manage your rules.'
      handleDelete={handleDelete}>
      <div className='AknGrid AknGrid--unclickable'>
        <div className='AknGrid-bodyRow AknGrid-bodyRow--highlight'>
          <div className='AknGrid-bodyCell'>
            {/* It is not translated since it is temporary. */}
            The value
            {Array.isArray(action.value) && action.value.length > 1 && 's'}
            &nbsp;
            <span className='AknRule-attribute'>
              {displayValue(action.value)}
            </span>
            &nbsp;
            {Array.isArray(action.value) && action.value.length > 1
              ? 'are'
              : 'is'}
            &nbsp;set into&nbsp;
            <FallbackField
              field={action.field}
              scope={action.scope}
              locale={action.locale}
            />
            .
          </div>
        </div>
      </div>
    </ActionTemplate>
  );
};

export { SetActionLine };
