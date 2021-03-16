import React, {ChangeEvent, Ref, useCallback} from 'react';
import styled, {css} from 'styled-components';
import {InputProps} from '../InputProps';
import {LockIcon} from '../../../icons';
import {Override} from '../../../shared';
import {AkeneoThemedProps, getColor, getFontSize} from '../../../theme';
import {RichTextEditor, EditorProps} from './RichTextEditor';

const TextAreaInputContainer = styled.div`
  position: relative;
  display: flex;
  flex-direction: column;
  width: 100%;
`;

const CommonStyle = css<{readOnly: boolean; invalid: boolean} & AkeneoThemedProps>`
  border: 1px solid ${({invalid}) => (invalid ? getColor('red', 100) : getColor('grey', 80))};
  border-radius: 2px;
  color: ${({readOnly}) => (readOnly ? getColor('grey', 100) : getColor('grey', 140))};
  font-size: ${getFontSize('default')};
  line-height: 20px;
  width: 100%;
  box-sizing: border-box;
  font-family: inherit;
  outline-style: none;
  background: ${({readOnly}) => (readOnly ? getColor('grey', 20) : getColor('white'))};

  &:focus-within {
    box-shadow: 0 0 0 2px ${getColor('blue', 40)};
  }
`;

const RichTextEditorContainer = styled.div<{readOnly: boolean; invalid: boolean} & AkeneoThemedProps>`
  ${CommonStyle}
  padding: 0;
  padding-bottom: 10px;

  & .rdw-editor-main {
    min-height: 200px;
    max-height: 400px;
    padding: 0 30px 10px 15px;
  }

  & .rdw-option-wrapper {
    min-width: 30px;
    height: 30px;
  }

  & .rdw-editor-toolbar {
    border: none;
    padding: 0;
    margin: 0;
    padding: 5px 30px 0 0;
    border-radius: 0;
    border-bottom: 1px solid ${({invalid}) => (invalid ? getColor('red', 100) : getColor('grey', 80))};
  }

  & .rdw-dropdown-wrapper:hover,
  & .rdw-option-wrapper:hover,
  & .rdw-dropdown-optionwrapper:hover {
    box-shadow: none;
  }
`;

const Textarea = styled.textarea<{readOnly: boolean; invalid: boolean} & AkeneoThemedProps>`
  ${CommonStyle}
  resize: none;
  height: 200px;
  padding: 10px 30px 10px 15px;

  &::placeholder {
    opacity: 1;
    color: ${getColor('grey', 100)};
  }
`;

const ReadOnlyIcon = styled(LockIcon)`
  position: absolute;
  right: 0;
  top: 0;
  margin: 12px;
  color: ${getColor('grey', 100)};
`;

const CharacterLeftLabel = styled.div`
  font-size: ${getFontSize('small')};
  align-self: flex-end;
  color: ${getColor('grey', 100)};
`;

type TextAreaInputProps = Override<
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
     * Placeholder displayed when the input is empty.
     */
    placeholder?: string;

    /**
     * Defines if the input is valid on not.
     */
    invalid?: boolean;

    /**
     * Label displayed under the field to display the remaining character counter.
     */
    characterLeftLabel?: string;

    /**
     * If true, the component will display a WYSIWYG editor.
     */
    isRichText?: boolean;

    /**
     * Properties passed to the WYSIWYG editor.
     */
    richTextEditorProps?: EditorProps;
  }
>;

/**
 * The TextAreaInput component allows the user to enter large text content and can also display a Rich Text Editor.
 */
const TextAreaInput = React.forwardRef<HTMLInputElement, TextAreaInputProps>(
  (
    {
      value,
      invalid,
      onChange,
      readOnly,
      characterLeftLabel,
      isRichText = false,
      richTextEditorProps,
      ...rest
    }: TextAreaInputProps,
    forwardedRef: Ref<HTMLInputElement>
  ) => {
    const handleChange = useCallback(
      (event: ChangeEvent<HTMLInputElement>) => {
        if (!readOnly && onChange) onChange(event.currentTarget.value);
      },
      [readOnly, onChange]
    );

    return (
      <TextAreaInputContainer>
        {isRichText ? (
          <RichTextEditorContainer readOnly={readOnly} invalid={invalid}>
            <RichTextEditor
              readOnly={readOnly}
              value={value}
              {...richTextEditorProps}
              onChange={value => onChange?.(value)}
            />
          </RichTextEditorContainer>
        ) : (
          <Textarea
            ref={forwardedRef}
            value={value}
            onChange={handleChange}
            type="text"
            readOnly={readOnly}
            disabled={readOnly}
            aria-invalid={invalid}
            invalid={invalid}
            {...rest}
          />
        )}
        {readOnly && <ReadOnlyIcon size={16} />}
        {characterLeftLabel && <CharacterLeftLabel>{characterLeftLabel}</CharacterLeftLabel>}
      </TextAreaInputContainer>
    );
  }
);

export {TextAreaInput};
export type {TextAreaInputProps};
