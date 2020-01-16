import * as React from 'react';
import {ContentState, convertToRaw, EditorState} from 'draft-js';
import __ from 'akeneoassetmanager/tools/translator';

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

const blockRenderer = (block: any, config: any): any => {
  if (block.getType() !== 'atomic') {
    return undefined;
  }

  const contentState = config.getEditorState().getCurrentContent();
  const entity = contentState.getEntity(block.getEntityAt(0));

  if (entity && entity.type === 'EMBEDDED_LINK') {
    const {src, height, width} = entity.getData();
    if (!src.startsWith('javascript:') && !src.startsWith('data:')) {
      return undefined;
    }

    return {
      component: () => (
        <div className="AknMessageBox AknMessageBox--danger">
          <div className="AknMessageBox-title">{__('pim_asset_manager.editor.embedded.invalid_link.title')}</div>
          <p>{__('pim_asset_manager.editor.embedded.invalid_link.message')}</p>
          <pre style={{textOverflow: 'ellipsis', maxWidth: 650, overflow: 'hidden'}}>
            {`<iframe height="${height}" width="${width}" src="${src}" />`}
          </pre>
        </div>
      ),
      editable: false,
    };
  }

  return undefined;
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
          customBlockRenderFunc={blockRenderer}
        />
      </React.Fragment>
    );
  }
}
