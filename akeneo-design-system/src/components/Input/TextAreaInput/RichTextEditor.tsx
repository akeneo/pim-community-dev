import React, {useEffect, useState} from 'react';
import {Editor} from 'react-draft-wysiwyg';
import draftToHtml from 'draftjs-to-html';
import {ContentState, convertToRaw, EditorState, convertFromHTML} from 'draft-js';
import 'react-draft-wysiwyg/dist/react-draft-wysiwyg.css';

const editorStateToRaw = (editorState: EditorState): string =>
  draftToHtml(convertToRaw(editorState.getCurrentContent()));

const rawToEditorState = (value: string): EditorState => {
  const rawDraft = convertFromHTML(value);

  if (!rawDraft) {
    return EditorState.createEmpty();
  }

  return EditorState.createWithContent(ContentState.createFromBlockArray(rawDraft.contentBlocks));
};

type RichTextEditorProps = {
  value: string;
  onChange: (value: string) => void;
};

const RichTextEditor = ({value, onChange}: RichTextEditorProps) => {
  const [editorState, setEditorState] = useState<EditorState>(rawToEditorState(value));

  useEffect(() => {
    onChange(editorStateToRaw(editorState));
  }, [editorState]);

  return <Editor editorState={editorState} onEditorStateChange={setEditorState} />;
};

export {RichTextEditor};
