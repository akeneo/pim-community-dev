import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import styled from 'styled-components';
import {File as FileModel, isFileEmpty, isFileInStorage, createEmptyFile} from 'akeneoassetmanager/domain/model/file';
import {getImageDownloadUrl, getImageShowUrl, getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';
import imageUploader from 'akeneoassetmanager/infrastructure/uploader/image';
import loadImage from 'akeneoassetmanager/tools/image-loader';
import Trash from 'akeneoassetmanager/application/component/app/icon/trash';
import Download from 'akeneoassetmanager/application/component/app/icon/download';
import Import from 'akeneoassetmanager/application/component/app/illustration/import';
import Key from 'akeneoassetmanager/tools/key';
import {MediaPreviewType} from 'akeneoassetmanager/domain/model/asset/media-preview';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {getLabelInCollection} from 'akeneoassetmanager/domain/model/label-collection';
import LocaleReference, {localeReferenceStringValue} from 'akeneoassetmanager/domain/model/locale-reference';
import ChannelReference from 'akeneoassetmanager/domain/model/channel-reference';
import {FullscreenPreview} from 'akeneoassetmanager/application/component/asset/edit/preview/fullscreen-preview';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import Fullscreen from 'akeneoassetmanager/application/component/app/icon/fullscreen';

const Img = styled.img`
  margin: auto;
  width: 100%;
  max-height: 140px;
  transition: filter 0.3s;
  z-index: 0;
  object-fit: contain;
`;

const Anchor = styled.span.attrs(() => ({className: 'AknImage-actionItem'}))``;
const ImageAction = styled.div.attrs(() => ({className: 'AknImage-action'}))`
  z-index: 1000;
`;

class FileComponent extends React.Component<
  {
    id?: string;
    context: {
      channel: ChannelReference;
      locale: LocaleReference;
    };
    image: FileModel;
    attribute: NormalizedAttribute;
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
    if ((undefined === removeEvent.key || Key.Backspace === removeEvent.key) && !isFileEmpty(this.props.image)) {
      this.stopEvent(event);
      this.setState({dropping: false});
      if (undefined !== this.props.onImageChange) {
        this.props.onImageChange(createEmptyFile());
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
    const {id, context, image, attribute, wide, readOnly, onImageChange} = this.props;

    const url = getMediaPreviewUrl({
      type: wide ? MediaPreviewType.Preview : MediaPreviewType.ThumbnailSmall,
      attributeIdentifier: attribute.identifier,
      data: image?.filePath || '',
    });
    const label = getLabelInCollection(
      attribute.labels,
      localeReferenceStringValue(context.locale),
      true,
      attribute.code
    );
    const previewModel = {
      data: image,
      channel: context.channel,
      locale: context.locale,
      attribute: attribute.identifier,
    };

    // If the image is in read only mode, we return a simple version of the component
    if (undefined === onImageChange) {
      const className = `AknImage AknImage--readOnly ${wide ? 'AknImage--wide' : ''}`;

      return (
        <div className={className}>
          {!isFileEmpty(image) && <div className="AknImage-drop" style={{backgroundImage: `url("${url}")`}} />}
          {true === wide ? <img className="AknImage-display" src={url} /> : <Img src={url} />}
        </div>
      );
    }

    const className = `AknImage AknImage--editable
      ${isFileEmpty(image) ? 'AknImage--empty' : ''}
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
        {!isFileEmpty(image) && <div className="AknImage-drop" style={{backgroundImage: `url("${url}")`}} />}
        <input
          id={id}
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
          disabled={readOnly}
        />
        {!isFileEmpty(image) && (
          <ImageAction>
            {!readOnly && (
              <span className="AknImage-actionItem" onClick={this.remove.bind(this)}>
                <Trash color={akeneoTheme.color.white} className="AknImage-actionItemIcon" />
                {__(`pim_asset_manager.app.image.${wide ? 'wide' : 'small'}.remove`)}
              </span>
            )}
            {isFileInStorage(image) && (
              <a className="AknImage-actionItem" href={getImageDownloadUrl(image)} tabIndex={-1}>
                <Download color={akeneoTheme.color.white} className="AknImage-actionItemIcon" />
                {__(`pim_asset_manager.app.image.${wide ? 'wide' : 'small'}.download`)}
              </a>
            )}
            <FullscreenPreview anchor={Anchor} label={label} previewModel={previewModel} attribute={attribute}>
              <Fullscreen
                title={__('pim_asset_manager.asset.button.fullscreen')}
                color={akeneoTheme.color.white}
                className="AknImage-actionItemIcon"
              />
              {__('pim_asset_manager.asset.button.fullscreen')}
            </FullscreenPreview>
          </ImageAction>
        )}
        {this.state.loading && (
          <div className={`AknImage-loader ${this.state.loading ? 'AknImage-loader--loading' : ''}`} style={style}>
            <div
              className="AknImage-drop"
              style={{
                backgroundImage: 0 !== this.state.uploadingImage.length ? `url("${this.state.uploadingImage}")` : '',
              }}
            />
          </div>
        )}
        {!isFileEmpty(image) && (
          <div className="AknImage-displayContainer">
            {true === wide ? <img className="AknImage-display" src={url} /> : <Img src={url} />}
          </div>
        )}
        {isFileEmpty(image) && undefined !== onImageChange && (
          <div className="AknImage-uploader">
            <Import className="AknImage-uploaderIllustration" />
            <span className="AknImage-uploaderHelper">
              {__(`pim_asset_manager.app.image.${wide ? 'wide' : 'small'}.upload`)}
            </span>
          </div>
        )}
      </div>
    );
  }
}

export default FileComponent;
