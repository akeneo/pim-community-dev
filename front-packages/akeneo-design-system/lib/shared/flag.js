"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.getEmoji = void 0;
var flags = {
    AD: {
        emoji: '🇦🇩',
        name: 'Andorra',
    },
    AE: {
        emoji: '🇦🇪',
        name: 'United Arab Emirates',
    },
    AF: {
        emoji: '🇦🇫',
        name: 'Afghanistan',
    },
    AG: {
        emoji: '🇦🇬',
        name: 'Antigua and Barbuda',
    },
    AI: {
        emoji: '🇦🇮',
        name: 'Anguilla',
    },
    AL: {
        emoji: '🇦🇱',
        name: 'Albania',
    },
    AM: {
        emoji: '🇦🇲',
        name: 'Armenia',
    },
    AO: {
        emoji: '🇦🇴',
        name: 'Angola',
    },
    AQ: {
        emoji: '🇦🇶',
        name: 'Antarctica',
    },
    AR: {
        emoji: '🇦🇷',
        name: 'Argentina',
    },
    AS: {
        emoji: '🇦🇸',
        name: 'American Samoa',
    },
    AT: {
        emoji: '🇦🇹',
        name: 'Austria',
    },
    AU: {
        emoji: '🇦🇺',
        name: 'Australia',
    },
    AW: {
        emoji: '🇦🇼',
        name: 'Aruba',
    },
    AX: {
        emoji: '🇦🇽',
        name: 'Åland Islands',
    },
    AZ: {
        emoji: '🇦🇿',
        name: 'Azerbaijan',
    },
    BA: {
        emoji: '🇧🇦',
        name: 'Bosnia and Herzegovina',
    },
    BB: {
        emoji: '🇧🇧',
        name: 'Barbados',
    },
    BD: {
        emoji: '🇧🇩',
        name: 'Bangladesh',
    },
    BE: {
        emoji: '🇧🇪',
        name: 'Belgium',
    },
    BF: {
        emoji: '🇧🇫',
        name: 'Burkina Faso',
    },
    BG: {
        emoji: '🇧🇬',
        name: 'Bulgaria',
    },
    BH: {
        emoji: '🇧🇭',
        name: 'Bahrain',
    },
    BI: {
        emoji: '🇧🇮',
        name: 'Burundi',
    },
    BJ: {
        emoji: '🇧🇯',
        name: 'Benin',
    },
    BL: {
        emoji: '🇧🇱',
        name: 'Saint Barthélemy',
    },
    BM: {
        emoji: '🇧🇲',
        name: 'Bermuda',
    },
    BN: {
        emoji: '🇧🇳',
        name: 'Brunei Darussalam',
    },
    BO: {
        emoji: '🇧🇴',
        name: 'Bolivia',
    },
    BQ: {
        emoji: '🇧🇶',
        name: 'Bonaire, Sint Eustatius and Saba',
    },
    BR: {
        emoji: '🇧🇷',
        name: 'Brazil',
    },
    BS: {
        emoji: '🇧🇸',
        name: 'Bahamas',
    },
    BT: {
        emoji: '🇧🇹',
        name: 'Bhutan',
    },
    BV: {
        emoji: '🇧🇻',
        name: 'Bouvet Island',
    },
    BW: {
        emoji: '🇧🇼',
        name: 'Botswana',
    },
    BY: {
        emoji: '🇧🇾',
        name: 'Belarus',
    },
    BZ: {
        emoji: '🇧🇿',
        name: 'Belize',
    },
    CA: {
        emoji: '🇨🇦',
        name: 'Canada',
    },
    CC: {
        emoji: '🇨🇨',
        name: 'Cocos (Keeling) Islands',
    },
    CD: {
        emoji: '🇨🇩',
        name: 'Congo',
    },
    CF: {
        emoji: '🇨🇫',
        name: 'Central African Republic',
    },
    CG: {
        emoji: '🇨🇬',
        name: 'Congo',
    },
    CH: {
        emoji: '🇨🇭',
        name: 'Switzerland',
    },
    CI: {
        emoji: '🇨🇮',
        name: 'Côte Ivoire',
    },
    CK: {
        emoji: '🇨🇰',
        name: 'Cook Islands',
    },
    CL: {
        emoji: '🇨🇱',
        name: 'Chile',
    },
    CM: {
        emoji: '🇨🇲',
        name: 'Cameroon',
    },
    CN: {
        emoji: '🇨🇳',
        name: 'China',
    },
    CO: {
        emoji: '🇨🇴',
        name: 'Colombia',
    },
    CR: {
        emoji: '🇨🇷',
        name: 'Costa Rica',
    },
    CU: {
        emoji: '🇨🇺',
        name: 'Cuba',
    },
    CV: {
        emoji: '🇨🇻',
        name: 'Cape Verde',
    },
    CW: {
        emoji: '🇨🇼',
        name: 'Curaçao',
    },
    CX: {
        emoji: '🇨🇽',
        name: 'Christmas Island',
    },
    CY: {
        emoji: '🇨🇾',
        name: 'Cyprus',
    },
    CZ: {
        emoji: '🇨🇿',
        name: 'Czech Republic',
    },
    DE: {
        emoji: '🇩🇪',
        name: 'Germany',
    },
    DJ: {
        emoji: '🇩🇯',
        name: 'Djibouti',
    },
    DK: {
        emoji: '🇩🇰',
        name: 'Denmark',
    },
    DM: {
        emoji: '🇩🇲',
        name: 'Dominica',
    },
    DO: {
        emoji: '🇩🇴',
        name: 'Dominican Republic',
    },
    DZ: {
        emoji: '🇩🇿',
        name: 'Algeria',
    },
    EC: {
        emoji: '🇪🇨',
        name: 'Ecuador',
    },
    EE: {
        emoji: '🇪🇪',
        name: 'Estonia',
    },
    EG: {
        emoji: '🇪🇬',
        name: 'Egypt',
    },
    EH: {
        emoji: '🇪🇭',
        name: 'Western Sahara',
    },
    ER: {
        emoji: '🇪🇷',
        name: 'Eritrea',
    },
    ES: {
        emoji: '🇪🇸',
        name: 'Spain',
    },
    ET: {
        emoji: '🇪🇹',
        name: 'Ethiopia',
    },
    EU: {
        emoji: '🇪🇺',
        name: 'European Union',
    },
    FI: {
        emoji: '🇫🇮',
        name: 'Finland',
    },
    FJ: {
        emoji: '🇫🇯',
        name: 'Fiji',
    },
    FK: {
        emoji: '🇫🇰',
        name: 'Falkland Islands (Malvinas)',
    },
    FM: {
        emoji: '🇫🇲',
        name: 'Micronesia',
    },
    FO: {
        emoji: '🇫🇴',
        name: 'Faroe Islands',
    },
    FR: {
        emoji: '🇫🇷',
        name: 'France',
    },
    GA: {
        emoji: '🇬🇦',
        name: 'Gabon',
    },
    GB: {
        emoji: '🇬🇧',
        name: 'United Kingdom',
    },
    GD: {
        emoji: '🇬🇩',
        name: 'Grenada',
    },
    GE: {
        emoji: '🇬🇪',
        name: 'Georgia',
    },
    GF: {
        emoji: '🇬🇫',
        name: 'French Guiana',
    },
    GG: {
        emoji: '🇬🇬',
        name: 'Guernsey',
    },
    GH: {
        emoji: '🇬🇭',
        name: 'Ghana',
    },
    GI: {
        emoji: '🇬🇮',
        name: 'Gibraltar',
    },
    GL: {
        emoji: '🇬🇱',
        name: 'Greenland',
    },
    GM: {
        emoji: '🇬🇲',
        name: 'Gambia',
    },
    GN: {
        emoji: '🇬🇳',
        name: 'Guinea',
    },
    GP: {
        emoji: '🇬🇵',
        name: 'Guadeloupe',
    },
    GQ: {
        emoji: '🇬🇶',
        name: 'Equatorial Guinea',
    },
    GR: {
        emoji: '🇬🇷',
        name: 'Greece',
    },
    GS: {
        emoji: '🇬🇸',
        name: 'South Georgia',
    },
    GT: {
        emoji: '🇬🇹',
        name: 'Guatemala',
    },
    GU: {
        emoji: '🇬🇺',
        name: 'Guam',
    },
    GW: {
        emoji: '🇬🇼',
        name: 'Guinea-Bissau',
    },
    GY: {
        emoji: '🇬🇾',
        name: 'Guyana',
    },
    HK: {
        emoji: '🇭🇰',
        name: 'Hong Kong',
    },
    HM: {
        emoji: '🇭🇲',
        name: 'Heard Island and Mcdonald Islands',
    },
    HN: {
        emoji: '🇭🇳',
        name: 'Honduras',
    },
    HR: {
        emoji: '🇭🇷',
        name: 'Croatia',
    },
    HT: {
        emoji: '🇭🇹',
        name: 'Haiti',
    },
    HU: {
        emoji: '🇭🇺',
        name: 'Hungary',
    },
    ID: {
        emoji: '🇮🇩',
        name: 'Indonesia',
    },
    IE: {
        emoji: '🇮🇪',
        name: 'Ireland',
    },
    IL: {
        emoji: '🇮🇱',
        name: 'Israel',
    },
    IM: {
        emoji: '🇮🇲',
        name: 'Isle of Man',
    },
    IN: {
        emoji: '🇮🇳',
        name: 'India',
    },
    IO: {
        emoji: '🇮🇴',
        name: 'British Indian Ocean Territory',
    },
    IQ: {
        emoji: '🇮🇶',
        name: 'Iraq',
    },
    IR: {
        emoji: '🇮🇷',
        name: 'Iran',
    },
    IS: {
        emoji: '🇮🇸',
        name: 'Iceland',
    },
    IT: {
        emoji: '🇮🇹',
        name: 'Italy',
    },
    JE: {
        emoji: '🇯🇪',
        name: 'Jersey',
    },
    JM: {
        emoji: '🇯🇲',
        name: 'Jamaica',
    },
    JO: {
        emoji: '🇯🇴',
        name: 'Jordan',
    },
    JP: {
        emoji: '🇯🇵',
        name: 'Japan',
    },
    KE: {
        emoji: '🇰🇪',
        name: 'Kenya',
    },
    KG: {
        emoji: '🇰🇬',
        name: 'Kyrgyzstan',
    },
    KH: {
        emoji: '🇰🇭',
        name: 'Cambodia',
    },
    KI: {
        emoji: '🇰🇮',
        name: 'Kiribati',
    },
    KM: {
        emoji: '🇰🇲',
        name: 'Comoros',
    },
    KN: {
        emoji: '🇰🇳',
        name: 'Saint Kitts and Nevis',
    },
    KP: {
        emoji: '🇰🇵',
        name: 'North Korea',
    },
    KR: {
        emoji: '🇰🇷',
        name: 'South Korea',
    },
    KW: {
        emoji: '🇰🇼',
        name: 'Kuwait',
    },
    KY: {
        emoji: '🇰🇾',
        name: 'Cayman Islands',
    },
    KZ: {
        emoji: '🇰🇿',
        name: 'Kazakhstan',
    },
    LA: {
        emoji: '🇱🇦',
        name: 'Lao People Democratic Republic',
    },
    LB: {
        emoji: '🇱🇧',
        name: 'Lebanon',
    },
    LC: {
        emoji: '🇱🇨',
        name: 'Saint Lucia',
    },
    LI: {
        emoji: '🇱🇮',
        name: 'Liechtenstein',
    },
    LK: {
        emoji: '🇱🇰',
        name: 'Sri Lanka',
    },
    LR: {
        emoji: '🇱🇷',
        name: 'Liberia',
    },
    LS: {
        emoji: '🇱🇸',
        name: 'Lesotho',
    },
    LT: {
        emoji: '🇱🇹',
        name: 'Lithuania',
    },
    LU: {
        emoji: '🇱🇺',
        name: 'Luxembourg',
    },
    LV: {
        emoji: '🇱🇻',
        name: 'Latvia',
    },
    LY: {
        emoji: '🇱🇾',
        name: 'Libya',
    },
    MA: {
        emoji: '🇲🇦',
        name: 'Morocco',
    },
    MC: {
        emoji: '🇲🇨',
        name: 'Monaco',
    },
    MD: {
        emoji: '🇲🇩',
        name: 'Moldova',
    },
    ME: {
        emoji: '🇲🇪',
        name: 'Montenegro',
    },
    MF: {
        emoji: '🇲🇫',
        name: 'Saint Martin (French Part)',
    },
    MG: {
        emoji: '🇲🇬',
        name: 'Madagascar',
    },
    MH: {
        emoji: '🇲🇭',
        name: 'Marshall Islands',
    },
    MK: {
        emoji: '🇲🇰',
        name: 'Macedonia',
    },
    ML: {
        emoji: '🇲🇱',
        name: 'Mali',
    },
    MM: {
        emoji: '🇲🇲',
        name: 'Myanmar',
    },
    MN: {
        emoji: '🇲🇳',
        name: 'Mongolia',
    },
    MO: {
        emoji: '🇲🇴',
        name: 'Macao',
    },
    MP: {
        emoji: '🇲🇵',
        name: 'Northern Mariana Islands',
    },
    MQ: {
        emoji: '🇲🇶',
        name: 'Martinique',
    },
    MR: {
        emoji: '🇲🇷',
        name: 'Mauritania',
    },
    MS: {
        emoji: '🇲🇸',
        name: 'Montserrat',
    },
    MT: {
        emoji: '🇲🇹',
        name: 'Malta',
    },
    MU: {
        emoji: '🇲🇺',
        name: 'Mauritius',
    },
    MV: {
        emoji: '🇲🇻',
        name: 'Maldives',
    },
    MW: {
        emoji: '🇲🇼',
        name: 'Malawi',
    },
    MX: {
        emoji: '🇲🇽',
        name: 'Mexico',
    },
    MY: {
        emoji: '🇲🇾',
        name: 'Malaysia',
    },
    MZ: {
        emoji: '🇲🇿',
        name: 'Mozambique',
    },
    NA: {
        emoji: '🇳🇦',
        name: 'Namibia',
    },
    NC: {
        emoji: '🇳🇨',
        name: 'New Caledonia',
    },
    NE: {
        emoji: '🇳🇪',
        name: 'Niger',
    },
    NF: {
        emoji: '🇳🇫',
        name: 'Norfolk Island',
    },
    NG: {
        emoji: '🇳🇬',
        name: 'Nigeria',
    },
    NI: {
        emoji: '🇳🇮',
        name: 'Nicaragua',
    },
    NL: {
        emoji: '🇳🇱',
        name: 'Netherlands',
    },
    NO: {
        emoji: '🇳🇴',
        name: 'Norway',
    },
    NP: {
        emoji: '🇳🇵',
        name: 'Nepal',
    },
    NR: {
        emoji: '🇳🇷',
        name: 'Nauru',
    },
    NU: {
        emoji: '🇳🇺',
        name: 'Niue',
    },
    NZ: {
        emoji: '🇳🇿',
        name: 'New Zealand',
    },
    OM: {
        emoji: '🇴🇲',
        name: 'Oman',
    },
    PA: {
        emoji: '🇵🇦',
        name: 'Panama',
    },
    PE: {
        emoji: '🇵🇪',
        name: 'Peru',
    },
    PF: {
        emoji: '🇵🇫',
        name: 'French Polynesia',
    },
    PG: {
        emoji: '🇵🇬',
        name: 'Papua New Guinea',
    },
    PH: {
        emoji: '🇵🇭',
        name: 'Philippines',
    },
    PK: {
        emoji: '🇵🇰',
        name: 'Pakistan',
    },
    PL: {
        emoji: '🇵🇱',
        name: 'Poland',
    },
    PM: {
        emoji: '🇵🇲',
        name: 'Saint Pierre and Miquelon',
    },
    PN: {
        emoji: '🇵🇳',
        name: 'Pitcairn',
    },
    PR: {
        emoji: '🇵🇷',
        name: 'Puerto Rico',
    },
    PS: {
        emoji: '🇵🇸',
        name: 'Palestinian Territory',
    },
    PT: {
        emoji: '🇵🇹',
        name: 'Portugal',
    },
    PW: {
        emoji: '🇵🇼',
        name: 'Palau',
    },
    PY: {
        emoji: '🇵🇾',
        name: 'Paraguay',
    },
    QA: {
        emoji: '🇶🇦',
        name: 'Qatar',
    },
    RE: {
        emoji: '🇷🇪',
        name: 'Réunion',
    },
    RO: {
        emoji: '🇷🇴',
        name: 'Romania',
    },
    RS: {
        emoji: '🇷🇸',
        name: 'Serbia',
    },
    RU: {
        emoji: '🇷🇺',
        name: 'Russia',
    },
    RW: {
        emoji: '🇷🇼',
        name: 'Rwanda',
    },
    SA: {
        emoji: '🇸🇦',
        name: 'Saudi Arabia',
    },
    SB: {
        emoji: '🇸🇧',
        name: 'Solomon Islands',
    },
    SC: {
        emoji: '🇸🇨',
        name: 'Seychelles',
    },
    SD: {
        emoji: '🇸🇩',
        name: 'Sudan',
    },
    SE: {
        emoji: '🇸🇪',
        name: 'Sweden',
    },
    SG: {
        emoji: '🇸🇬',
        name: 'Singapore',
    },
    SH: {
        emoji: '🇸🇭',
        name: 'Saint Helena, Ascension and Tristan Da Cunha',
    },
    SI: {
        emoji: '🇸🇮',
        name: 'Slovenia',
    },
    SJ: {
        emoji: '🇸🇯',
        name: 'Svalbard and Jan Mayen',
    },
    SK: {
        emoji: '🇸🇰',
        name: 'Slovakia',
    },
    SL: {
        emoji: '🇸🇱',
        name: 'Sierra Leone',
    },
    SM: {
        emoji: '🇸🇲',
        name: 'San Marino',
    },
    SN: {
        emoji: '🇸🇳',
        name: 'Senegal',
    },
    SO: {
        emoji: '🇸🇴',
        name: 'Somalia',
    },
    SR: {
        emoji: '🇸🇷',
        name: 'Suriname',
    },
    SS: {
        emoji: '🇸🇸',
        name: 'South Sudan',
    },
    ST: {
        emoji: '🇸🇹',
        name: 'Sao Tome and Principe',
    },
    SV: {
        emoji: '🇸🇻',
        name: 'El Salvador',
    },
    SX: {
        emoji: '🇸🇽',
        name: 'Sint Maarten (Dutch Part)',
    },
    SY: {
        emoji: '🇸🇾',
        name: 'Syrian Arab Republic',
    },
    SZ: {
        emoji: '🇸🇿',
        name: 'Swaziland',
    },
    TC: {
        emoji: '🇹🇨',
        name: 'Turks and Caicos Islands',
    },
    TD: {
        emoji: '🇹🇩',
        name: 'Chad',
    },
    TF: {
        emoji: '🇹🇫',
        name: 'French Southern Territories',
    },
    TG: {
        emoji: '🇹🇬',
        name: 'Togo',
    },
    TH: {
        emoji: '🇹🇭',
        name: 'Thailand',
    },
    TJ: {
        emoji: '🇹🇯',
        name: 'Tajikistan',
    },
    TK: {
        emoji: '🇹🇰',
        name: 'Tokelau',
    },
    TL: {
        emoji: '🇹🇱',
        name: 'Timor-Leste',
    },
    TM: {
        emoji: '🇹🇲',
        name: 'Turkmenistan',
    },
    TN: {
        emoji: '🇹🇳',
        name: 'Tunisia',
    },
    TO: {
        emoji: '🇹🇴',
        name: 'Tonga',
    },
    TR: {
        emoji: '🇹🇷',
        name: 'Turkey',
    },
    TT: {
        emoji: '🇹🇹',
        name: 'Trinidad and Tobago',
    },
    TV: {
        emoji: '🇹🇻',
        name: 'Tuvalu',
    },
    TW: {
        emoji: '🇹🇼',
        name: 'Taiwan',
    },
    TZ: {
        emoji: '🇹🇿',
        name: 'Tanzania',
    },
    UA: {
        emoji: '🇺🇦',
        name: 'Ukraine',
    },
    UG: {
        emoji: '🇺🇬',
        name: 'Uganda',
    },
    UM: {
        emoji: '🇺🇲',
        name: 'United States Minor Outlying Islands',
    },
    US: {
        emoji: '🇺🇸',
        name: 'United States',
    },
    UY: {
        emoji: '🇺🇾',
        name: 'Uruguay',
    },
    UZ: {
        emoji: '🇺🇿',
        name: 'Uzbekistan',
    },
    VA: {
        emoji: '🇻🇦',
        name: 'Vatican City',
    },
    VC: {
        emoji: '🇻🇨',
        name: 'Saint Vincent and The Grenadines',
    },
    VE: {
        emoji: '🇻🇪',
        name: 'Venezuela',
    },
    VG: {
        emoji: '🇻🇬',
        name: 'Virgin Islands, British',
    },
    VI: {
        emoji: '🇻🇮',
        name: 'Virgin Islands, U.S.',
    },
    VN: {
        emoji: '🇻🇳',
        name: 'Viet Nam',
    },
    VU: {
        emoji: '🇻🇺',
        name: 'Vanuatu',
    },
    WF: {
        emoji: '🇼🇫',
        name: 'Wallis and Futuna',
    },
    WS: {
        emoji: '🇼🇸',
        name: 'Samoa',
    },
    XK: {
        emoji: '🇽🇰',
        name: 'Kosovo',
    },
    YE: {
        emoji: '🇾🇪',
        name: 'Yemen',
    },
    YT: {
        emoji: '🇾🇹',
        name: 'Mayotte',
    },
    ZA: {
        emoji: '🇿🇦',
        name: 'South Africa',
    },
    ZM: {
        emoji: '🇿🇲',
        name: 'Zambia',
    },
    ZW: {
        emoji: '🇿🇼',
        name: 'Zimbabwe',
    },
};
var getEmoji = function (countryCode) { var _a; return (_a = flags[countryCode.toUpperCase()]) === null || _a === void 0 ? void 0 : _a.emoji; };
exports.getEmoji = getEmoji;
//# sourceMappingURL=flag.js.map