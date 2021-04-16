import React, {useState} from 'react';
import {Editor, EditorProps} from 'react-draft-wysiwyg';
import draftToHtml from 'draftjs-to-html';
import htmlToDraft from 'html-to-draftjs';
import {ContentState, convertToRaw, EditorState} from 'draft-js';
import 'react-draft-wysiwyg/dist/react-draft-wysiwyg.css';
import {Override} from '../../../shared';

const editorStateToRaw = (editorState: EditorState): string =>
  draftToHtml(convertToRaw(editorState.getCurrentContent()));

const rawToEditorState = (value: string): EditorState => {
  const rawDraft = htmlToDraft(value);

  if (!rawDraft || !rawDraft.contentBlocks) {
    return EditorState.createEmpty();
  }

  return EditorState.createWithContent(ContentState.createFromBlockArray(rawDraft.contentBlocks));
};

type RichTextEditorProps = Override<
  EditorProps,
  {
    value: string;
    readOnly?: boolean;
    onChange: (value: string) => void;
  }
>;

const RichTextEditor = ({value, readOnly = false, onChange, ...rest}: RichTextEditorProps) => {
  const [editorState, setEditorState] = useState<EditorState>(rawToEditorState(value));

  const handleChange = (editorState: EditorState) => {
    setEditorState(editorState);
    onChange(editorStateToRaw(editorState));
  };

  return (
    <Editor
      toolbarHidden={readOnly}
      readOnly={readOnly}
      toolbar={{
        options: ['inline', 'blockType', 'fontSize', 'fontFamily', 'list', 'link', 'embedded', 'image', 'remove'],
        inline: {
          options: ['bold', 'italic'],
        },
      }}
      onEditorStateChange={handleChange}
      {...rest}
      editorState={editorState}
    />
  );
};

export {RichTextEditor};
export type {EditorProps};
