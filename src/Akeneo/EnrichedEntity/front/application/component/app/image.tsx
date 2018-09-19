import * as React from 'react';
import FileModel from 'akeneoenrichedentity/domain/model/file';
import {getImageShowUrl} from 'akeneoenrichedentity/tools/media-url-generator';
import imageUploader from 'akeneoenrichedentity/infrastructure/uploader/image';
import loadImage from 'akeneoenrichedentity/tools/image-loader';

class Image extends React.Component<
  {
    image: FileModel;
    alt: string;
    wide?: boolean;
    onImageChange: (image: FileModel) => void;
  },
  {
    dropping: boolean;
    removing: boolean;
    loading: boolean;
    focusing: boolean;
    ratio: number;
    uploadingImage: string;
  }
> {
  public state = {dropping: false, removing: false, focusing: false, loading: false, ratio: 0, uploadingImage: ''};
  public uploadingFile = null;

  private stopEvent = (event: any) => {
    event.preventDefault();
    event.stopPropagation();
  };

  private overStart = () => {
    if (!this.props.image.isEmpty()) {
      this.setState({removing: true});
    } else {
      this.setState({removing: false, dropping: true});
    }
  };

  private overStop = () => {
    this.setState({removing: false, dropping: false});
  };

  private focusStart = () => {
    this.setState({focusing: true});
  };

  private focusStop = () => {
    this.setState({focusing: false});
  };

  private drop = (event: React.DragEvent<HTMLInputElement>) => {
    this.stopEvent(event);
    this.dragStop();
    this.upload(event.dataTransfer.files[0]);
  };

  private dragStart = () => {
    this.setState({dropping: true});
  };

  private dragStop = () => {
    this.setState({dropping: false});
  };

  private click = (event: React.MouseEvent<HTMLInputElement>) => {
    if (!this.props.image.isEmpty()) {
      this.stopEvent(event);
      this.setState({removing: false, dropping: true});
      this.props.onImageChange(FileModel.createEmpty());
    }
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
      this.props.onImageChange(image);
    } catch (error) {
      console.error(error);
    }

    this.setState({loading: false});
  };

  render() {
    const wide = undefined === this.props.wide ? false : this.props.wide;
    const className = `AknImage AknImage--editable
      ${this.state.dropping && !this.state.loading ? 'AknImage--dropping' : ''}
      ${this.state.removing && !this.state.loading ? 'AknImage--removing' : ''}
      ${this.state.focusing ? 'AknImage--focusing' : ''}
      ${wide ? 'AknImage--wide' : ''}
    `;

    const imageUrl = getImageShowUrl(this.props.image, true === this.props.wide ? 'preview' : 'thumbnail');
    const style =
      0 === this.state.ratio
        ? {width: `${this.state.ratio * 100}%`, transition: 'width 0s'}
        : {width: `${this.state.ratio * 100}%`};
    return (
      <div className={className}>
        {true === this.props.wide && !this.props.image.isEmpty() ? (
          <div className="AknImage-drop" style={{backgroundImage: `url("${imageUrl}")`}} />
        ) : null}
        <input
          className="AknImage-updater"
          onDrag={this.stopEvent}
          onDragStart={this.stopEvent}
          onDragEnd={this.dragStop.bind(this)}
          onDragOver={this.dragStart.bind(this)}
          onDragEnter={this.dragStart.bind(this)}
          onDragLeave={this.dragStop.bind(this)}
          onDrop={this.drop.bind(this)}
          onChange={this.change.bind(this)}
          onMouseEnter={this.overStart.bind(this)}
          onMouseLeave={this.overStop.bind(this)}
          onClick={this.click.bind(this)}
          onFocus={this.focusStart.bind(this)}
          onBlur={this.focusStop.bind(this)}
          type="file"
          value=""
        />
        <div
          ref="loader"
          className={`AknImage-loader ${this.state.loading ? 'AknImage-loader--loading' : ''}`}
          style={style}
        >
          <div className="AknImage-drop" style={{backgroundImage: `url("${this.state.uploadingImage}")`}} />
        </div>
        <img className="AknImage-display" src={imageUrl} />
      </div>
    );
  }
}

export default Image;
