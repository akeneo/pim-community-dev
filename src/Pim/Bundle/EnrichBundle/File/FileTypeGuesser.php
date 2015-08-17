<?php

namespace Pim\Bundle\EnrichBundle\File;

use Pim\Bundle\CatalogBundle\AttributeType\FileType;

/**
 * Filetype guesser interface implementation
 *
 * @see https://www.iana.org/assignments/media-types/media-types.xhtml
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FileTypeGuesser implements FileTypeGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function guess($mimeType)
    {
        switch ($mimeType) {
            case 'image/bmp':
            case 'image/cgm':
            case 'image/example':
            case 'image/fits':
            case 'image/g3fax':
            case 'image/gif':
            case 'image/jp2':
            case 'image/jpeg':
            case 'image/jpm':
            case 'image/jpx':
            case 'image/naplps':
            case 'image/pjpeg':
            case 'image/png':
            case 'image/prs.btif':
            case 'image/prs.pti':
            case 'image/pwg-raster':
            case 'image/t38':
            case 'image/tiff':
            case 'image/tiff-fx':
            case 'image/vnd-djvu':
            case 'image/vnd-svf':
            case 'image/vnd-wap-wbmp':
            case 'image/vnd.adobe.photoshop':
            case 'image/vnd.airzip.accelerator.azv':
            case 'image/vnd.cns.inf2':
            case 'image/vnd.dece.graphic':
            case 'image/vnd.dvb.subtitle':
            case 'image/vnd.dwg':
            case 'image/vnd.dxf':
            case 'image/vnd.fastbidsheet':
            case 'image/vnd.fpx':
            case 'image/vnd.fst':
            case 'image/vnd.fujixerox.edmics-mmr':
            case 'image/vnd.fujixerox.edmics-rlc':
            case 'image/vnd.globalgraphics.pgb':
            case 'image/vnd.microsoft.icon':
            case 'image/vnd.mix':
            case 'image/vnd.mozilla.apng':
            case 'image/vnd.ms-modi':
            case 'image/vnd.net-fpx':
            case 'image/vnd.radiance':
            case 'image/vnd.sealed-png':
            case 'image/vnd.sealedmedia.softseal-gif':
            case 'image/vnd.sealedmedia.softseal-jpg':
            case 'image/vnd.tencent.tap':
            case 'image/vnd.valve.source.texture':
            case 'image/vnd.xiff':
            case 'image/vnd.zbrush.pcx':
            case 'image/x-png':
                $type = FileTypes::IMAGE;
                break;

            case 'application/msword':
            case 'application/pdf':
            case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
            case 'application/vnd.ms-excel':
            case 'application/x-pdf':
            case 'text/1d-interleaved-parityfec':
            case 'text/cache-manifest':
            case 'text/calendar':
            case 'text/css':
            case 'text/csv':
            case 'text/csv-schema':
            case 'text/directory':
            case 'text/dns':
            case 'text/ecmascript':
            case 'text/encaprtp':
            case 'text/example':
            case 'text/fwdred':
            case 'text/grammar-ref-list':
            case 'text/html':
            case 'text/javascript':
            case 'text/jcr-cnd':
            case 'text/markdown':
            case 'text/mizar':
            case 'text/n3':
            case 'text/parameters':
            case 'text/plain':
            case 'text/provenance-notation':
            case 'text/prs.fallenstein.rst':
            case 'text/prs.lines.tag':
            case 'text/raptorfec':
            case 'text/RED':
            case 'text/rfc822-headers':
            case 'text/rtf':
            case 'text/rtp-enc-aescm128':
            case 'text/rtploopback':
            case 'text/rtx':
            case 'text/SGML':
            case 'text/t140':
            case 'text/tab-separated-values':
            case 'text/troff':
            case 'text/turtle':
            case 'text/ulpfec':
            case 'text/uri-list':
            case 'text/vcard':
            case 'text/vnd-a':
            case 'text/vnd-curl':
            case 'text/vnd.abc':
            case 'text/vnd.debian.copyright':
            case 'text/vnd.DMClientScript':
            case 'text/vnd.dvb.subtitle':
            case 'text/vnd.esmertec.theme-descriptor':
            case 'text/vnd.fly':
            case 'text/vnd.fmi.flexstor':
            case 'text/vnd.graphviz':
            case 'text/vnd.in3d.3dml':
            case 'text/vnd.in3d.spot':
            case 'text/vnd.IPTC.NewsML':
            case 'text/vnd.IPTC.NITF':
            case 'text/vnd.latex-z':
            case 'text/vnd.motorola.reflex':
            case 'text/vnd.ms-mediapackage':
            case 'text/vnd.net2phone.commcenter.command':
            case 'text/vnd.radisys.msml-basic-layout':
            case 'text/vnd.si.uricatalogue':
            case 'text/vnd.sun.j2me.app-descriptor':
            case 'text/vnd.trolltech.linguist':
            case 'text/vnd.wap-wml':
            case 'text/vnd.wap.si':
            case 'text/vnd.wap.sl':
            case 'text/vnd.wap.wmlscript':
            case 'text/xml':
            case 'text/xml-external-parsed-entity':
                $type = FileTypes::TEXT;
                break;

            case 'video/1d-interleaved-parityfec':
            case 'video/3gpp':
            case 'video/3gpp-tt':
            case 'video/3gpp2':
            case 'video/BMPEG':
            case 'video/BT656':
            case 'video/CelB':
            case 'video/DV':
            case 'video/encaprtp':
            case 'video/example':
            case 'video/H261':
            case 'video/H263':
            case 'video/H263-1998':
            case 'video/H263-2000':
            case 'video/H264':
            case 'video/H264-RCDO':
            case 'video/H264-SVC':
            case 'video/iso.segment':
            case 'video/JPEG':
            case 'video/jpeg2000':
            case 'video/mj2':
            case 'video/MP1S':
            case 'video/MP2P':
            case 'video/MP2T':
            case 'video/mp4':
            case 'video/MP4V-ES':
            case 'video/mpeg':
            case 'video/mpeg4-generic':
            case 'video/MPV':
            case 'video/msvideo':
            case 'video/nv':
            case 'video/ogg':
            case 'video/pointer':
            case 'video/quicktime':
            case 'video/raptorfec':
            case 'video/rtp-enc-aescm128':
            case 'video/rtploopback':
            case 'video/rtx':
            case 'video/SMPTE292M':
            case 'video/ulpfec':
            case 'video/vc1':
            case 'video/vnd.avi':
            case 'video/vnd-mpegurl':
            case 'video/vnd-vivo':
            case 'video/vnd.CCTV':
            case 'video/vnd.dece-mp4':
            case 'video/vnd.dece.hd':
            case 'video/vnd.dece.mobile':
            case 'video/vnd.dece.pd':
            case 'video/vnd.dece.sd':
            case 'video/vnd.dece.video':
            case 'video/vnd.directv-mpeg':
            case 'video/vnd.directv.mpeg-tts':
            case 'video/vnd.dlna.mpeg-tts':
            case 'video/vnd.dvb.file':
            case 'video/vnd.fvt':
            case 'video/vnd.hns.video':
            case 'video/vnd.motorola.video':
            case 'video/vnd.motorola.videop':
            case 'video/vnd.ms-playready.media.pyv':
            case 'video/vnd.nokia.interleaved-multimedia':
            case 'video/vnd.nokia.videovoip':
            case 'video/vnd.objectvideo':
            case 'video/vnd.radgamettools.bink':
            case 'video/vnd.radgamettools.smacker':
            case 'video/vnd.sealed-swf':
            case 'video/vnd.sealed.mpeg1':
            case 'video/vnd.sealed.mpeg4':
            case 'video/vnd.sealedmedia.softseal-mov':
            case 'video/vnd.uvvu-mp4':
            case 'video/webm':
            case 'video/x-f4v':
            case 'video/x-flv':
            case 'video/x-ms-wmv':
            case 'video/x-msvideo':
                $type = FileTypes::VIDEO;
                break;

            default:
                $type = FileTypes::UNKNOWN;
        }

        return $type;
    }
}
