import * as React from "react";
import {EditorState, ContentState, convertToRaw} from 'draft-js';
import {Editor} from 'react-draft-wysiwyg';
import * as draftToHtml from 'draftjs-to-html';
import htmlToDraft from 'html-to-draftjs';

type RichTextEditorProps = {value: string, onChange: (value: string) => void};
type RichTextEditorState = {editorState?: any};

const draftToRaw = (editorState: any) => {
  return draftToHtml(convertToRaw(editorState.getCurrentContent()))
}

const rawToEditorState = (value: string) => {
  const contentBlock = htmlToDraft(value);
  if (contentBlock) {
    const contentState = ContentState.createFromBlockArray(contentBlock.contentBlocks);
    const editorState = EditorState.createWithContent(contentState);

    return {editorState}
  }

  return {}
}

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

  static getDerivedStateFromProps(props: RichTextEditorProps, state: RichTextEditorState): RichTextEditorState {
    if (props.value !== draftToRaw(state.editorState)) {
      return rawToEditorState(props.value);
    }

    return state;
  }

  render(): JSX.Element | JSX.Element[] {
    const { editorState } = this.state;
    return (
      <React.Fragment>
        <Editor
          editorState={editorState}
          editorClassName="AknTextareaField"
          onEditorStateChange={this.onEditorStateChange}
        />
      </React.Fragment>
    );
  }
}
