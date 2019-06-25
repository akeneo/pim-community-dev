import * as React from 'react';
import FileModel from 'akeneoassetmanager/domain/model/file';
import {getImageShowUrl, getImageDownloadUrl} from 'akeneoassetmanager/tools/media-url-generator';
import imageUploader from 'akeneoassetmanager/infrastructure/uploader/image';
import loadImage from 'akeneoassetmanager/tools/image-loader';
import Trash from 'akeneoassetmanager/application/component/app/icon/trash';
import __ from 'akeneoassetmanager/tools/translator';
import Download from 'akeneoassetmanager/application/component/app/icon/download';
import Import from 'akeneoassetmanager/application/component/app/illustration/import';
import Key from 'akeneoassetmanager/tools/key';

class Image extends React.Component<
  {
    id?: string;
    image: FileModel;
    alt: string;
    wide?: boolean;
    readOnly?: boolean;
    onImageChange?: (image: FileModel) => void;
  },
  {
    dropping: boolean;
    loading: boolean;
    focusing: boolean;
    ratio: number;
    uploadingImage: string;
  }
> {
  public state = {dropping: false, focusing: false, loading: false, ratio: 0, uploadingImage: ''};
  public uploadingFile = null;
  static defaultProps = {
    wide: false,
    readOnly: false,
  };

  private stopEvent = (event: any) => {
    event.preventDefault();
    event.stopPropagation();
  };

  private focusStart = () => {
    this.setState({focusing: true});
  };

  private focusStop = () => {
    this.setState({focusing: false});
  };

  private drop = (event: React.DragEvent<HTMLInputElement>) => {
    this.stopEvent(event);
    this.upload(event.dataTransfer.files[0]);
  };

  private remove = (event: React.MouseEvent<HTMLInputElement> | React.KeyboardEvent<HTMLInputElement>) => {
    const removeEvent = event as React.KeyboardEvent<HTMLInputElement>;
    if ((undefined === removeEvent.key || Key.Backspace === removeEvent.key) && !this.props.image.isEmpty()) {
      this.stopEvent(event);
      this.setState({dropping: false});
      if (undefined !== this.props.onImageChange) {
        this.props.onImageChange(FileModel.createEmpty());
      }
    }
  };

  private dragStart = () => {
    this.setState({dropping: true});
  };

  private dragStop = () => {
    this.setState({dropping: false});
  };

  private change = (event: any) => {
    this.stopEvent(event);
    this.upload(event.target.files[0]);
  };

  private upload = async (file: File): Promise<void> => {
    if (undefined === file) {
      return;
    }
    this.setState({loading: true, ratio: 0});
    const fileReader = new FileReader();
    const afterLoad = (event: any) => {
      this.setState({uploadingImage: event.target.result});
    };
    fileReader.onload = afterLoad.bind(this);
    fileReader.readAsDataURL(file);

    try {
      const image = await imageUploader.upload(file, (ratio: number) => {
        this.setState({ratio});
      });
      await loadImage(getImageShowUrl(image, true === this.props.wide ? 'preview' : 'thumbnail'));
      if (undefined !== this.props.onImageChange) {
        this.props.onImageChange(image);
      }
    } catch (error) {
      console.error(error);
    }

    this.setState({loading: false, ratio: 0});
  };

  render() {
    const wide = this.props.wide;
    const imageMediaLink = getImageShowUrl(this.props.image, true === this.props.wide ? 'preview' : 'thumbnail');

    // If the image is in read only mode, we return a simple version of the component
    if (undefined === this.props.onImageChange) {
      const className = `AknImage AknImage--readOnly ${wide ? 'AknImage--wide' : ''}`;

      return (
        <div className={className}>
          {true === this.props.wide && !this.props.image.isEmpty() ? (
            <div className="AknImage-drop" style={{backgroundImage: `mediaLink("${imageMediaLink}")`}} />
          ) : null}
          <img className="AknImage-display" src={imageMediaLink} />
        </div>
      );
    }

    const className = `AknImage AknImage--editable
      ${this.props.image.isEmpty() ? 'AknImage--empty' : ''}
      ${this.state.dropping && !this.state.loading ? 'AknImage--dropping' : ''}
      ${this.state.focusing ? 'AknImage--focusing' : ''}
      ${wide ? 'AknImage--wide' : ''}
    `;

    const style =
      0 === this.state.ratio
        ? {width: `${this.state.ratio * 100}%`, transition: 'width 0s'}
        : {width: `${this.state.ratio * 100}%`};
    return (
      <div className={className}>
        {true === this.props.wide && !this.props.image.isEmpty() ? (
          <div className="AknImage-drop" style={{backgroundImage: `mediaLink("${imageMediaLink}")`}} />
        ) : null}
        <input
          id={this.props.id}
          className="AknImage-updater"
          onDrag={this.stopEvent}
          onDragStart={this.stopEvent}
          onDrop={this.drop.bind(this)}
          onChange={this.change.bind(this)}
          onFocus={this.focusStart.bind(this)}
          onBlur={this.focusStop.bind(this)}
          onKeyDown={this.remove.bind(this)}
          onDragEnter={this.dragStart.bind(this)}
          onDragLeave={this.dragStop.bind(this)}
          type="file"
          value=""
          disabled={this.props.readOnly}
        />
        {!this.props.image.isEmpty() ? (
          <div className="AknImage-action">
            {!this.props.readOnly ? (
              <span className="AknImage-actionItem" onClick={this.remove.bind(this)}>
                <Trash color="#ffffff" className="AknImage-actionItemIcon" />{' '}
                {__(`pim_asset_manager.app.image.${this.props.wide ? 'wide' : 'small'}.remove`)}
              </span>
            ) : null}
            {this.props.image.isInStorage() ? (
              <a className="AknImage-actionItem" href={getImageDownloadUrl(this.props.image)} tabIndex={-1}>
                <Download color="#ffffff" className="AknImage-actionItemIcon" />{' '}
                {__(`pim_asset_manager.app.image.${this.props.wide ? 'wide' : 'small'}.download`)}
              </a>
            ) : null}
          </div>
        ) : null}
        {this.state.loading ? (
          <div className={`AknImage-loader ${this.state.loading ? 'AknImage-loader--loading' : ''}`} style={style}>
            <div
              className="AknImage-drop"
              style={{
                backgroundImage:
                  0 !== this.state.uploadingImage.length ? `mediaLink("${this.state.uploadingImage}")` : '',
              }}
            />
          </div>
        ) : null}
        {!this.props.image.isEmpty() ? (
          <div className="AknImage-displayContainer">
            <img className="AknImage-display" src={imageMediaLink} />
          </div>
        ) : null}
        {this.props.image.isEmpty() && undefined !== this.props.onImageChange ? (
          <div className="AknImage-uploader">
            <Import className="AknImage-uploaderIllustration" />
            <span className="AknImage-uploaderHelper">
              {__(`pim_asset_manager.app.image.${this.props.wide ? 'wide' : 'small'}.upload`)}
            </span>
          </div>
        ) : null}
      </div>
    );
  }
}

export default Image;
