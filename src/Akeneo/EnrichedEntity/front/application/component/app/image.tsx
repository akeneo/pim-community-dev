import * as React from 'react';
import ImageModel from 'akeneoenrichedentity/domain/model/image';
import {getImageShowUrl} from 'akeneoenrichedentity/tools/media-url-generator';
import imageUploader from 'akeneoenrichedentity/infrastructure/uploader/image';
import loadImage from 'akeneoenrichedentity/tools/image-loader';

class Image extends React.Component<{
  image: ImageModel|null,
  alt: string,
  onImageChange: (image: ImageModel|null) => void
}, {
  dropping: boolean,
  removing: boolean,
  loading: boolean,
  focusing: boolean,
  ratio: number
}> {
  public state = {dropping: false, removing: false, focusing: false, loading: false, ratio: 0};

  private stopEvent = (event: any) => {
    event.preventDefault();
    event.stopPropagation();
  }

  private overStart = () => {
    if (null !== this.props.image) {
      this.setState({removing: true});
    } else {
      this.setState({removing: false, dropping: true});
    }
  }

  private overStop = () => {
    this.setState({removing: false, dropping: false});
  }

  private focusStart = () => {
    this.setState({focusing: true});
  }

  private focusStop = () => {
    this.setState({focusing: false});
  }

  private drop = (event: any) => {
    this.stopEvent(event);
    this.dragStop();
    this.upload(event.dataTransfer.files[0]);
  }

  private dragStart = () => {
    this.setState({dropping: true});
  }

  private dragStop = () => {
    this.setState({dropping: false});
  }

  private click = (event: any) => {
    if (null !== this.props.image) {
      this.stopEvent(event);
      this.setState({removing: false, dropping: true});
      this.props.onImageChange(null);
    }
  }

  private change = (event: any) => {
    this.stopEvent(event);
    this.upload(event.target.files[0]);
  }

  private upload = async (file: File): Promise<void> => {
    if (undefined === file) {
      return;
    }
    this.setState({loading: true, ratio: 0});

    try {
      const image = await imageUploader.upload(file, (ratio: number) => {
        this.setState({ratio});
      });
      await loadImage(getImageShowUrl(image, 'thumbnail'));
      this.props.onImageChange(image);
    } catch (error) {
      console.error(error);
    }

    this.setState({loading: false, ratio: 0});
  }

  render() {
    const className = `AknTitleContainer-imageContainer AknTitleContainer-imageContainer--editable
      ${this.state.dropping && !this.state.loading ? 'AknTitleContainer-imageContainer--dropping' : ''}
      ${this.state.removing && !this.state.loading ? 'AknTitleContainer-imageContainer--removing' : ''}
      ${this.state.focusing ? 'AknTitleContainer-imageContainer--focusing' : ''}
    `;

    return (
      <div className={className}>
        <input
          className="AknTitleContainer-imageUpdater"
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
        <div className="AknTitleContainer-imageLoader" style={{width: `${this.state.ratio * 100}%`, opacity: this.state.loading ? 1 : 0}}/>
        <img className="AknTitleContainer-image" src={getImageShowUrl(this.props.image, 'thumbnail')} />
      </div>
    );
  }
};

export default Image;
