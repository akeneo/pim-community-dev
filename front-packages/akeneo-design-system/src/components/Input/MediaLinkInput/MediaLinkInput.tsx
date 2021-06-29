import React, {ChangeEvent, Ref, useRef, isValidElement, cloneElement, useState, useEffect} from 'react';
import styled, {css} from 'styled-components';
import {Key, Override} from '../../../shared';
import {InputProps} from '../InputProps';
import {AkeneoThemedProps, getColor} from '../../../theme';
import {DefaultPictureIllustration} from '../../../illustrations';
import {IconButton, IconButtonProps, Image} from '../../../components';
import {LockIcon} from '../../../icons';
import {useShortcut} from '../../../hooks';
import DefaultPicture from '../../../../static/illustrations/DefaultPicture.svg';

const MediaLinkInputContainer = styled.div<{readOnly: boolean} & AkeneoThemedProps>`
  position: relative;
  display: flex;
  flex-direction: row;
  align-items: center;
  padding: 12px;
  border: 1px solid ${({invalid}) => (invalid ? getColor('red', 100) : getColor('grey', 80))};
  border-radius: 2px;
  height: 74px;
  gap: 10px;
  outline-style: none;
  box-sizing: border-box;
  background: ${({readOnly}) => (readOnly ? getColor('grey', 20) : getColor('white'))};
  cursor: ${({readOnly}) => (readOnly ? 'not-allowed' : 'auto')};
  overflow: hidden;
  ${({readOnly}) =>
    !readOnly &&
    css`
      &:focus-within {
        box-shadow: 0 0 0 2px ${getColor('blue', 40)};
      }
    `}
`;

const Input = styled.input<{readOnly: boolean} & AkeneoThemedProps>`
  border: none;
  flex: 1;
  outline: none;
  color: ${({readOnly}) => (readOnly ? getColor('grey', 100) : getColor('grey', 140))};
  background: transparent;
  cursor: ${({readOnly}) => (readOnly ? 'not-allowed' : 'auto')};
  height: 100%;

  &::placeholder {
    opacity: 1;
    color: ${getColor('grey', 100)};
  }
`;

const ReadOnlyIcon = styled(LockIcon)`
  margin-left: 4px;
`;

const ActionContainer = styled.div`
  display: flex;
  gap: 2px;
  align-items: center;
  color: ${getColor('grey', 100)};
`;

const MediaLinkImage = styled(Image)`
  border: none;
`;

type PreviewType = 'preview' | 'thumbnail';

type MediaLinkInputProps = Override<
  Override<React.InputHTMLAttributes<HTMLInputElement>, InputProps<string>>,
  (
    | {
        readOnly: true;
      }
    | {
        readOnly?: boolean;
        onChange: (newValue: string) => void;
      }
  ) & {
    /**
     * Value of the input.
     */
    value: string;

    /**
     * Url of the thumbnail (can be base64).
     */
    thumbnailUrl: string | null;

    /**
     * Placeholder displayed when the input is empty.
     */
    placeholder?: string;

    /**
     * Defines if the input is valid or not.
     */
    invalid?: boolean;

    /**
     * Callback called when the user hit enter on the field.
     */
    onSubmit?: () => void;
  }
>;

/**
 * Media Link input allows the user to enter content when the expected user input is a link.
 */
const MediaLinkInput = React.forwardRef<HTMLInputElement, MediaLinkInputProps>(
  (
    {
      onChange,
      value,
      placeholder,
      thumbnailUrl,
      children,
      invalid = false,
      readOnly = false,
      onSubmit,
      ...rest
    }: MediaLinkInputProps,
    forwardedRef: Ref<HTMLInputElement>
  ) => {
    const internalRef = useRef<HTMLInputElement | null>(null);
    forwardedRef = forwardedRef ?? internalRef;
    const containerRef = useRef<HTMLDivElement>(null);
    const [displayedThumbnailUrl, setDisplayedThumbnailUrl] = useState(thumbnailUrl);

    useEffect(() => {
      setDisplayedThumbnailUrl(thumbnailUrl);
    }, [thumbnailUrl]);

    const actions = React.Children.map(children, child => {
      if (isValidElement<IconButtonProps>(child) && IconButton === child.type) {
        return cloneElement(child, {
          level: 'tertiary',
          ghost: 'borderless',
          size: 'small',
        });
      }

      return null;
    });

    const handleChange = (event: ChangeEvent<HTMLInputElement>) => {
      if (!readOnly && onChange) onChange(event.currentTarget.value);
    };

    const handleEnter = () => {
      !readOnly && onSubmit?.();
    };

    useShortcut(Key.Enter, handleEnter, forwardedRef);

    return (
      <>
        <MediaLinkInputContainer ref={containerRef} tabIndex={readOnly ? -1 : 0} invalid={invalid} readOnly={readOnly}>
          {'' !== value ? (
            <MediaLinkImage
              src={displayedThumbnailUrl}
              height={47}
              width={47}
              alt={value}
              onError={() => setDisplayedThumbnailUrl(DefaultPicture)}
            />
          ) : (
            <DefaultPictureIllustration title={placeholder} size={47} />
          )}
          <Input
            ref={forwardedRef}
            type="text"
            onChange={handleChange}
            readOnly={readOnly}
            disabled={readOnly}
            value={value}
            placeholder={placeholder}
            {...rest}
          />
          <ActionContainer>
            {'' !== value && actions}
            {readOnly && <ReadOnlyIcon size={16} />}
          </ActionContainer>
        </MediaLinkInputContainer>
      </>
    );
  }
);

export {MediaLinkInput};
export type {PreviewType};
