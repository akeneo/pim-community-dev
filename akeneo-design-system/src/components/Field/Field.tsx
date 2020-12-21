import React, {Ref, ReactElement} from 'react';
import styled from 'styled-components';
import {Helper, HelperProps, InputProps, Locale, LocaleProps} from '../../components';
import {getColor} from '../../theme';
import {useId, useSkeleton} from '../../hooks';
import {applySkeletonStyle, SkeletonProps} from '../Skeleton/Skeleton';

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

const Label = styled.label<SkeletonProps>`
  flex: 1;

  & > span {
    ${applySkeletonStyle()}
  }
`;

const Channel = styled.span<SkeletonProps>`
  text-transform: capitalize;

  :not(:last-child) {
    margin-right: 5px;
  }

  ${applySkeletonStyle()}
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
  locale?: ReactElement<LocaleProps> | string;

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
    const inputId = useId('input_');
    const labelId = useId('label_');

    const decoratedChildren = React.Children.map(children, child => {
      if (React.isValidElement<HelperProps>(child) && child.type === Helper) {
        return <HelperContainer>{React.cloneElement(child, {inline: true})}</HelperContainer>;
      }

      if (React.isValidElement<InputProps>(child)) {
        return React.cloneElement(child, {id: inputId, 'aria-labelledby': labelId});
      }

      return null;
    });

    const skeleton = useSkeleton();

    return (
      <FieldContainer ref={forwardedRef} {...rest}>
        <LabelContainer>
          {incomplete && !skeleton && <IncompleteBadge />}
          <Label htmlFor={inputId} id={labelId} skeleton={skeleton}>
            <span>{label}</span>
          </Label>
          {channel && <Channel skeleton={skeleton}>{channel}</Channel>}
          {locale && ('string' === typeof locale ? <Locale code={locale} /> : locale)}
        </LabelContainer>
        {decoratedChildren}
      </FieldContainer>
    );
  }
);

export {Field};
