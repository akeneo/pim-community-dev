import * as React from 'react';
import {EditorState, ContentState, convertToRaw} from 'draft-js';
const {Editor} = require('react-draft-wysiwyg');
const htmlToDraft = require('html-to-draftjs').default;
const draftToHtml = require('draftjs-to-html');

type RichTextEditorProps = {
  value: string;
  onChange: (value: string) => void;
  readOnly: boolean;
};

type RichTextEditorState = {editorState?: any};

const draftToRaw = (editorState: any) => {
  return draftToHtml(convertToRaw(editorState.getCurrentContent()));
};

const rawToEditorState = (value: string) => {
  const contentBlock = htmlToDraft(value);
  if (contentBlock) {
    const contentState = ContentState.createFromBlockArray(contentBlock.contentBlocks);
    const editorState = EditorState.createWithContent(contentState);

    return {editorState};
  }

  return {};
};

export default class RichTextEditor extends React.Component<RichTextEditorProps, RichTextEditorState> {
  constructor(props: RichTextEditorProps) {
    super(props);
    this.state = rawToEditorState(props.value);
  }

  onEditorStateChange: Function = (editorState: any) => {
    this.props.onChange(draftToRaw(editorState));
    this.setState({
      editorState,
    });
  };

  static getDerivedStateFromProps(props: RichTextEditorProps, state: RichTextEditorState) {
    const valueIsTheSame = props.value === draftToRaw(state.editorState);
    const editorIsInFocus = state.editorState.getSelection().getHasFocus();

    if (valueIsTheSame || editorIsInFocus) {
      return null;
    }

    return rawToEditorState(props.value);
  }

  render(): JSX.Element | JSX.Element[] {
    const {editorState} = this.state;
    return (
      <React.Fragment>
        <Editor
          toolbarHidden={this.props.readOnly}
          toolbar={{
            options: ['inline', 'blockType', 'fontSize', 'fontFamily', 'list', 'link', 'embedded', 'image', 'remove'],
            inline: {
              options: ['bold', 'italic'],
            },
          }}
          editorState={editorState}
          editorClassName="AknTextareaField AknTextareaField--light"
          onEditorStateChange={this.onEditorStateChange}
          readOnly={this.props.readOnly}
          disabled={this.props.readOnly}
        />
      </React.Fragment>
    );
  }
}
