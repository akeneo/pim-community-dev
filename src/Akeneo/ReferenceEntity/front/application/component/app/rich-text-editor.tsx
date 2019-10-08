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

console.log(draftToRaw)

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
    if (props.value === draftToRaw(state.editorState)) return;

    const { editorState } = rawToEditorState(props.value);

    if (editorState) {
      const originalSelection = state.editorState.getSelection();

      const updateSelection = editorState.getSelection().merge({
        anchorOffset: originalSelection.getAnchorOffset(),
        focusOffset: originalSelection.getFocusOffset(),
        isBackward: false,
      })

      const restoredSelection: any = editorState.getSelection().merge(updateSelection);

      return {
        editorState: originalSelection.getHasFocus() ?
        EditorState.forceSelection(editorState, restoredSelection) :
        EditorState.moveSelectionToEnd(editorState)
      }
    }

    return {};
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
