/* eslint-disable @typescript-eslint/no-var-requires */
import React from 'react';
import { ContentState, convertToRaw, EditorState } from 'draft-js';
import { Label } from '../Labels';
import { Editor } from 'react-draft-wysiwyg';
import draftToHtml from 'draftjs-to-html';
import htmlToDraft from 'html-to-draftjs';

const draftToRaw = (editorState: any) => {
  return draftToHtml(convertToRaw(editorState.getCurrentContent()) as any);
};

const rawToEditorState = (value: string) => {
  const contentBlock = htmlToDraft(value);
  const contentState = ContentState.createFromBlockArray(
    contentBlock.contentBlocks
  );

  return EditorState.createWithContent(contentState);
};

type Props = {
  value: string;
  label: string;
  onChange: (value: string) => void;
};

const InputWysiwygTextArea: React.FC<Props> = ({
  value,
  label,
  onChange,
  ...remainingProps
}) => {
  const [state, setState] = React.useState(rawToEditorState(value));

  const onEditorStateChange = (editorState: any) => {
    onChange(draftToRaw(editorState));
    setState(editorState);
  };

  return (
    <>
      <Label label={label} />
      <Editor
        editorClassName='AknTextareaField'
        editorState={state as any}
        onEditorStateChange={onEditorStateChange}
        toolbar={{
          options: ['inline', 'blockType', 'list', 'link'],
          inline: {
            options: ['bold', 'italic', 'underline'],
          },
          list: {
            options: ['unordered', 'ordered'],
          },
          blockType: {
            inDropdown: false,
            className: 'rdw-editor-toolbar-blockType',
            options: ['Code'],
          },
        }}
        {...remainingProps}
      />
    </>
  );
};

export { InputWysiwygTextArea };
