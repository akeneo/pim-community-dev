import React, {Ref, ReactElement} from 'react';
import styled from 'styled-components';
import {Helper, HelperProps, InputProps} from '../../components';
import {getColor, getFontSize} from '../../theme';
import {useId} from '../../hooks';
import {getLocale} from '../../shared';

const FieldContainer = styled.div`
  display: flex;
  flex-direction: column;
  max-width: 460px;
`;

const LabelContainer = styled.div`
  display: flex;
  align-items: center;
  line-height: 16px;
  margin-bottom: 8px;
  max-width: 460px;
`;

const Label = styled.label`
  flex: 1;
`;

const Channel = styled.span`
  text-transform: capitalize;
  margin-left: 5px;
`;

const Locale = styled.span`
  margin-left: 5px;
  font-size: ${getFontSize('bigger')};
`;

const HelperContainer = styled.div`
  margin-top: 5px;
  max-width: 460px;
`;

const IncompleteBadge = styled.div`
  border-radius: 50%;
  background-color: ${getColor('yellow', 100)};
  width: 8px;
  height: 8px;
  margin-right: 4px;
`;

type FieldChild = ReactElement<InputProps> | ReactElement<HelperProps>;

type FieldProps = {
  /**
   * The label of the field.
   */
  label: string;

  /**
   * Whether the field is complete or not.
   */
  incomplete?: boolean;

  /**
   * The locale of the field.
   */
  locale?: string;

  /**
   * The channel of the field.
   */
  channel?: string;

  /**
   * Children of the Field, can only be an Input or Helpers, other children will not be displayed.
   */
  children: FieldChild | FieldChild[];
};

/**
 * The Field component is used to display information around an Input component.
 */
const Field = React.forwardRef<HTMLDivElement, FieldProps>(
  ({label, locale, channel, incomplete = false, children, ...rest}: FieldProps, forwardedRef: Ref<HTMLDivElement>) => {
    const id = useId('field_');

    const decoratedChildren = React.Children.map(children, child => {
      if (React.isValidElement<HelperProps>(child) && child.type === Helper) {
        return <HelperContainer>{React.cloneElement(child, {inline: true})}</HelperContainer>;
      }

      if (React.isValidElement<InputProps>(child)) {
        return React.cloneElement(child, {id});
      }

      return null;
    });

    return (
      <FieldContainer ref={forwardedRef} {...rest}>
        <LabelContainer>
          {incomplete && <IncompleteBadge />}
          <Label htmlFor={id}>{label}</Label>
          {channel && <Channel>{channel}</Channel>}
          {locale && <Locale>{getLocale(locale)}</Locale>}
        </LabelContainer>
        {decoratedChildren}
      </FieldContainer>
    );
  }
);

export {Field};
