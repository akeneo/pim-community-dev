import React, {
  ChangeEvent,
  Ref,
  ReactNode,
  useRef,
  isValidElement,
  cloneElement,
  createElement,
  useState,
  useEffect,
  ReactElement,
} from 'react';
import styled, {css} from 'styled-components';
import {Key, Override} from '../../../shared';
import {InputProps} from '../InputProps';
import {AkeneoThemedProps, getColor} from '../../../theme';
import {DefaultPictureIllustration} from '../../../illustrations';
import {IconButton, IconButtonProps, Image, Button} from '../../../components';
import {FullscreenIcon, LockIcon} from '../../../icons';
import {useBooleanState, useShortcut} from '../../../hooks';
import {FullscreenPreview} from './FullscreenPreview';
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
      &:focus {
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
     * Component to render the preview.
     */
    preview: ReactNode;

    /**
     * Placeholder displayed when the input is empty.
     */
    placeholder?: string;

    /**
     * Title of the fullscreen icon button.
     */
    fullscreenTitle: string;

    /**
     * Label displayed at the top of the fullscreen preview.
     */
    fullscreenLabel?: string;

    /**
     * Title of the close icon button in the fullscreen preview.
     */
    closeTitle: string;

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
      preview,
      placeholder,
      thumbnailUrl,
      fullscreenTitle,
      fullscreenLabel,
      closeTitle,
      children,
      invalid = false,
      readOnly = false,
      onSubmit,
      ...rest
    }: MediaLinkInputProps,
    forwardedRef: Ref<HTMLInputElement>
  ) => {
    const containerRef = useRef<HTMLDivElement>(null);
    const [isFullScreenModalOpen, openFullScreenModal, closeFullScreenModal] = useBooleanState(false);
    const [displayedThumbnailUrl, setDisplayedThumbnailUrl] = useState(thumbnailUrl);
    const actions: ReactElement[] = [];
    const fullScreenActions: ReactElement[] = [];

    useEffect(() => {
      setDisplayedThumbnailUrl(thumbnailUrl);
    }, [thumbnailUrl]);

    React.Children.forEach(children, (child, index) => {
      if (isValidElement<IconButtonProps>(child) && IconButton === child.type) {
        actions.push(
          cloneElement(child, {
            key: index,
            level: 'tertiary',
            ghost: 'borderless',
            size: 'small',
          })
        );
        fullScreenActions.push(
          createElement(Button, {
            key: index,
            level: 'tertiary',
            ghost: true,
            children: [child.props.icon, child.props.title],
          })
        );
      }
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
            {'' !== value && (
              <>
                {actions}
                <IconButton
                  size="small"
                  level="tertiary"
                  ghost="borderless"
                  icon={<FullscreenIcon />}
                  title={fullscreenTitle}
                  onClick={openFullScreenModal}
                />
              </>
            )}
            {readOnly && <ReadOnlyIcon size={16} />}
          </ActionContainer>
        </MediaLinkInputContainer>
        {isFullScreenModalOpen && '' !== value && (
          <FullscreenPreview closeTitle={closeTitle} onClose={closeFullScreenModal}>
            <FullscreenPreview.Title>{fullscreenLabel ?? value}</FullscreenPreview.Title>
            <FullscreenPreview.Content>
              {preview}
              <FullscreenPreview.Actions>{fullScreenActions}</FullscreenPreview.Actions>
            </FullscreenPreview.Content>
          </FullscreenPreview>
        )}
      </>
    );
  }
);

export {MediaLinkInput};
export type {PreviewType};
