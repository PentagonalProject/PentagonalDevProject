<?php
if (! defined('BASEPATH')) {
    return;
}

class Processor
{
    private static $environment;

    /**
     * Default configuration
     *
     * @var array
     */
    protected $config_default = array(
        // disable migration
        'migration' => array(
            'migration_enabled' => false,
            'migration_type' => 'timestamp',
            'migration_table' => 'migrations',
            'migration_auto_latest' => false,
            'migration_version' => false,
            'migration_path' => null,
        ),
        'app' => array(
            'environment' => 'production',
            'base_url' => '',
            'index_page' => 'index.php',
            'uri_protocol' => 'REQUEST_URI',
            'url_suffix' => '',
            'language'	=> 'english',
            'charset' => 'UTF-8',
            'enable_hooks' => false,
            'subclass_prefix' => 'Pentagonal',
            'composer_autoload' => true,
            'permitted_uri_chars' => '',
            'allow_get_array' => true,
            'enable_query_strings' => false,
            'controller_trigger' => 'c',
            'function_trigger' => 'm',
            'directory_trigger' => 'd',
            'log_threshold' => 0,
            'log_path' => '',
            'log_file_extension' => '',
            'log_file_permissions' => 0644,
            'log_date_format' => 'Y-m-d H:i:s',
            'error_views_path' => '',
            'cache_path' => '',
            'cache_query_string' => false,
            'encryption_key' => '',
            'sess_driver' => 'files',
            'sess_cookie_name' => 'pentagonal_session',
            'sess_expiration' => null,
            'sess_save_path' => null,
            'sess_match_ip' => false,
            'sess_time_to_update' => 300,
            'sess_regenerate_destroy' => false,
            'cookie_prefix'	=> '',
            'cookie_domain'	=> '',
            'cookie_path' => '/',
            'cookie_secure'	=> false,
            'cookie_httponly' 	=> false,
            'standardize_newlines' => false,
            'global_xss_filtering' => false,
            'csrf_protection' => false,
            'csrf_token_name' => 'csrf_token',
            'csrf_cookie_name' => 'csrf_cookie',
            'csrf_expire' => null,
            'csrf_regenerate' => true,
            'csrf_exclude_uris' => array(),
            'compress_output' => false,
            'time_reference' => 'local',
            'rewrite_short_tags' => false,
            'proxy_ips' => '',
        ),
        'db' => array(
            'dsn'	=> '',
            'hostname' => 'localhost',
            'username' => '',
            'password' => '',
            'database' => '',
            'dbdriver' => 'mysqli',
            'dbprefix' => '',
            'pconnect' => false,
            'db_debug' => false,
            'cache_on' => false,
            'cachedir' => '',
            'char_set' => 'utf8',
            'dbcollat' => 'utf8_general_ci',
            'swap_pre' => '',
            'encrypt' => false,
            'compress' => false,
            'stricton' => false,
            'failover' => array(),
            'save_queries' => true
        ),
        'memcache' => array(
            'hostname' => '127.0.0.1',
            'port'     => '11211',
            'weight'   => '1',
        ),
        'doctype' => array(
            'xhtml11' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">',
            'xhtml1-strict' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
            'xhtml1-trans' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
            'xhtml1-frame' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">',
            'xhtml-basic11' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd">',
            'html5' => '<!DOCTYPE html>',
            'html4-strict' => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">',
            'html4-trans' => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">',
            'html4-frame' => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">',
            'mathml1' => '<!DOCTYPE math SYSTEM "http://www.w3.org/Math/DTD/mathml1/mathml.dtd">',
            'mathml2' => '<!DOCTYPE math PUBLIC "-//W3C//DTD MathML 2.0//EN" "http://www.w3.org/Math/DTD/mathml2/mathml2.dtd">',
            'svg10' => '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.0//EN" "http://www.w3.org/TR/2001/REC-SVG-20010904/DTD/svg10.dtd">',
            'svg11' => '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">',
            'svg11-basic' => '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1 Basic//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11-basic.dtd">',
            'svg11-tiny' => '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1 Tiny//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11-tiny.dtd">',
            'xhtml-math-svg-xh' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1 plus MathML 2.0 plus SVG 1.1//EN" "http://www.w3.org/2002/04/xhtml-math-svg/xhtml-math-svg.dtd">',
            'xhtml-math-svg-sh' => '<!DOCTYPE svg:svg PUBLIC "-//W3C//DTD XHTML 1.1 plus MathML 2.0 plus SVG 1.1//EN" "http://www.w3.org/2002/04/xhtml-math-svg/xhtml-math-svg.dtd">',
            'xhtml-rdfa-1' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">',
            'xhtml-rdfa-2' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.1//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-2.dtd">'
        ),
        'foreign_char' =>  array(
            '/ä|æ|ǽ/' => 'ae',
            '/ö|œ/' => 'oe',
            '/ü/' => 'ue',
            '/Ä/' => 'Ae',
            '/Ü/' => 'Ue',
            '/Ö/' => 'Oe',
            '/À|Á|Â|Ã|Ä|Å|Ǻ|Ā|Ă|Ą|Ǎ|Α|Ά|Ả|Ạ|Ầ|Ẫ|Ẩ|Ậ|Ằ|Ắ|Ẵ|Ẳ|Ặ|А/' => 'A',
            '/à|á|â|ã|å|ǻ|ā|ă|ą|ǎ|ª|α|ά|ả|ạ|ầ|ấ|ẫ|ẩ|ậ|ằ|ắ|ẵ|ẳ|ặ|а/' => 'a',
            '/Б/' => 'B',
            '/б/' => 'b',
            '/Ç|Ć|Ĉ|Ċ|Č/' => 'C',
            '/ç|ć|ĉ|ċ|č/' => 'c',
            '/Д/' => 'D',
            '/д/' => 'd',
            '/Ð|Ď|Đ|Δ/' => 'Dj',
            '/ð|ď|đ|δ/' => 'dj',
            '/È|É|Ê|Ë|Ē|Ĕ|Ė|Ę|Ě|Ε|Έ|Ẽ|Ẻ|Ẹ|Ề|Ế|Ễ|Ể|Ệ|Е|Э/' => 'E',
            '/è|é|ê|ë|ē|ĕ|ė|ę|ě|έ|ε|ẽ|ẻ|ẹ|ề|ế|ễ|ể|ệ|е|э/' => 'e',
            '/Ф/' => 'F',
            '/ф/' => 'f',
            '/Ĝ|Ğ|Ġ|Ģ|Γ|Г|Ґ/' => 'G',
            '/ĝ|ğ|ġ|ģ|γ|г|ґ/' => 'g',
            '/Ĥ|Ħ/' => 'H',
            '/ĥ|ħ/' => 'h',
            '/Ì|Í|Î|Ï|Ĩ|Ī|Ĭ|Ǐ|Į|İ|Η|Ή|Ί|Ι|Ϊ|Ỉ|Ị|И|Ы/' => 'I',
            '/ì|í|î|ï|ĩ|ī|ĭ|ǐ|į|ı|η|ή|ί|ι|ϊ|ỉ|ị|и|ы|ї/' => 'i',
            '/Ĵ/' => 'J',
            '/ĵ/' => 'j',
            '/Ķ|Κ|К/' => 'K',
            '/ķ|κ|к/' => 'k',
            '/Ĺ|Ļ|Ľ|Ŀ|Ł|Λ|Л/' => 'L',
            '/ĺ|ļ|ľ|ŀ|ł|λ|л/' => 'l',
            '/М/' => 'M',
            '/м/' => 'm',
            '/Ñ|Ń|Ņ|Ň|Ν|Н/' => 'N',
            '/ñ|ń|ņ|ň|ŉ|ν|н/' => 'n',
            '/Ò|Ó|Ô|Õ|Ō|Ŏ|Ǒ|Ő|Ơ|Ø|Ǿ|Ο|Ό|Ω|Ώ|Ỏ|Ọ|Ồ|Ố|Ỗ|Ổ|Ộ|Ờ|Ớ|Ỡ|Ở|Ợ|О/' => 'O',
            '/ò|ó|ô|õ|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º|ο|ό|ω|ώ|ỏ|ọ|ồ|ố|ỗ|ổ|ộ|ờ|ớ|ỡ|ở|ợ|о/' => 'o',
            '/П/' => 'P',
            '/п/' => 'p',
            '/Ŕ|Ŗ|Ř|Ρ|Р/' => 'R',
            '/ŕ|ŗ|ř|ρ|р/' => 'r',
            '/Ś|Ŝ|Ş|Ș|Š|Σ|С/' => 'S',
            '/ś|ŝ|ş|ș|š|ſ|σ|ς|с/' => 's',
            '/Ț|Ţ|Ť|Ŧ|τ|Т/' => 'T',
            '/ț|ţ|ť|ŧ|т/' => 't',
            '/Þ|þ/' => 'th',
            '/Ù|Ú|Û|Ũ|Ū|Ŭ|Ů|Ű|Ų|Ư|Ǔ|Ǖ|Ǘ|Ǚ|Ǜ|Ũ|Ủ|Ụ|Ừ|Ứ|Ữ|Ử|Ự|У/' => 'U',
            '/ù|ú|û|ũ|ū|ŭ|ů|ű|ų|ư|ǔ|ǖ|ǘ|ǚ|ǜ|υ|ύ|ϋ|ủ|ụ|ừ|ứ|ữ|ử|ự|у/' => 'u',
            '/Ý|Ÿ|Ŷ|Υ|Ύ|Ϋ|Ỳ|Ỹ|Ỷ|Ỵ|Й/' => 'Y',
            '/ý|ÿ|ŷ|ỳ|ỹ|ỷ|ỵ|й/' => 'y',
            '/В/' => 'V',
            '/в/' => 'v',
            '/Ŵ/' => 'W',
            '/ŵ/' => 'w',
            '/Ź|Ż|Ž|Ζ|З/' => 'Z',
            '/ź|ż|ž|ζ|з/' => 'z',
            '/Æ|Ǽ/' => 'AE',
            '/ß/' => 'ss',
            '/Ĳ/' => 'IJ',
            '/ĳ/' => 'ij',
            '/Œ/' => 'OE',
            '/ƒ/' => 'f',
            '/ξ/' => 'ks',
            '/π/' => 'p',
            '/β/' => 'v',
            '/μ/' => 'm',
            '/ψ/' => 'ps',
            '/Ё/' => 'Yo',
            '/ё/' => 'yo',
            '/Є/' => 'Ye',
            '/є/' => 'ye',
            '/Ї/' => 'Yi',
            '/Ж/' => 'Zh',
            '/ж/' => 'zh',
            '/Х/' => 'Kh',
            '/х/' => 'kh',
            '/Ц/' => 'Ts',
            '/ц/' => 'ts',
            '/Ч/' => 'Ch',
            '/ч/' => 'ch',
            '/Ш/' => 'Sh',
            '/ш/' => 'sh',
            '/Щ/' => 'Shch',
            '/щ/' => 'shch',
            '/Ъ|ъ|Ь|ь/' => '',
            '/Ю/' => 'Yu',
            '/ю/' => 'yu',
            '/Я/' => 'Ya',
            '/я/' => 'ya'
        ),
        'mime_type' => array(
            'hqx'	=>	array('resource/mac-binhex40', 'resource/mac-binhex', 'resource/x-binhex40', 'resource/x-mac-binhex40'),
            'cpt'	=>	'resource/mac-compactpro',
            'csv'	=>	array('text/x-comma-separated-values', 'text/comma-separated-values', 'resource/octet-stream', 'resource/vnd.ms-excel', 'resource/x-csv', 'text/x-csv', 'text/csv', 'resource/csv', 'resource/excel', 'resource/vnd.msexcel', 'text/plain'),
            'bin'	=>	array('resource/macbinary', 'resource/mac-binary', 'resource/octet-stream', 'resource/x-binary', 'resource/x-macbinary'),
            'dms'	=>	'resource/octet-stream',
            'lha'	=>	'resource/octet-stream',
            'lzh'	=>	'resource/octet-stream',
            'exe'	=>	array('resource/octet-stream', 'resource/x-msdownload'),
            'class'	=>	'resource/octet-stream',
            'psd'	=>	array('resource/x-photoshop', 'image/vnd.adobe.photoshop'),
            'so'	=>	'resource/octet-stream',
            'sea'	=>	'resource/octet-stream',
            'dll'	=>	'resource/octet-stream',
            'oda'	=>	'resource/oda',
            'pdf'	=>	array('resource/pdf', 'resource/force-download', 'resource/x-download', 'binary/octet-stream'),
            'ai'	=>	array('resource/pdf', 'resource/postscript'),
            'eps'	=>	'resource/postscript',
            'ps'	=>	'resource/postscript',
            'smi'	=>	'resource/smil',
            'smil'	=>	'resource/smil',
            'mif'	=>	'resource/vnd.mif',
            'xls'	=>	array('resource/vnd.ms-excel', 'resource/msexcel', 'resource/x-msexcel', 'resource/x-ms-excel', 'resource/x-excel', 'resource/x-dos_ms_excel', 'resource/xls', 'resource/x-xls', 'resource/excel', 'resource/download', 'resource/vnd.ms-office', 'resource/msword'),
            'ppt'	=>	array('resource/powerpoint', 'resource/vnd.ms-powerpoint', 'resource/vnd.ms-office', 'resource/msword'),
            'pptx'	=> 	array('resource/vnd.openxmlformats-officedocument.presentationml.presentation', 'resource/x-zip', 'resource/zip'),
            'wbxml'	=>	'resource/wbxml',
            'wmlc'	=>	'resource/wmlc',
            'dcr'	=>	'resource/x-director',
            'dir'	=>	'resource/x-director',
            'dxr'	=>	'resource/x-director',
            'dvi'	=>	'resource/x-dvi',
            'gtar'	=>	'resource/x-gtar',
            'gz'	=>	'resource/x-gzip',
            'gzip'  =>	'resource/x-gzip',
            'php'	=>	array('resource/x-httpd-php', 'resource/php', 'resource/x-php', 'text/php', 'text/x-php', 'resource/x-httpd-php-source'),
            'php4'	=>	'resource/x-httpd-php',
            'php3'	=>	'resource/x-httpd-php',
            'phtml'	=>	'resource/x-httpd-php',
            'phps'	=>	'resource/x-httpd-php-source',
            'js'	=>	array('resource/x-javascript', 'text/plain'),
            'swf'	=>	'resource/x-shockwave-flash',
            'sit'	=>	'resource/x-stuffit',
            'tar'	=>	'resource/x-tar',
            'tgz'	=>	array('resource/x-tar', 'resource/x-gzip-compressed'),
            'z'	=>	'resource/x-compress',
            'xhtml'	=>	'resource/xhtml+xml',
            'xht'	=>	'resource/xhtml+xml',
            'zip'	=>	array('resource/x-zip', 'resource/zip', 'resource/x-zip-compressed', 'resource/s-compressed', 'multipart/x-zip'),
            'rar'	=>	array('resource/x-rar', 'resource/rar', 'resource/x-rar-compressed'),
            'mid'	=>	'audio/midi',
            'midi'	=>	'audio/midi',
            'mpga'	=>	'audio/mpeg',
            'mp2'	=>	'audio/mpeg',
            'mp3'	=>	array('audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'),
            'aif'	=>	array('audio/x-aiff', 'audio/aiff'),
            'aiff'	=>	array('audio/x-aiff', 'audio/aiff'),
            'aifc'	=>	'audio/x-aiff',
            'ram'	=>	'audio/x-pn-realaudio',
            'rm'	=>	'audio/x-pn-realaudio',
            'rpm'	=>	'audio/x-pn-realaudio-plugin',
            'ra'	=>	'audio/x-realaudio',
            'rv'	=>	'video/vnd.rn-realvideo',
            'wav'	=>	array('audio/x-wav', 'audio/wave', 'audio/wav'),
            'bmp'	=>	array('image/bmp', 'image/x-bmp', 'image/x-bitmap', 'image/x-xbitmap', 'image/x-win-bitmap', 'image/x-windows-bmp', 'image/ms-bmp', 'image/x-ms-bmp', 'resource/bmp', 'resource/x-bmp', 'resource/x-win-bitmap'),
            'gif'	=>	'image/gif',
            'jpeg'	=>	array('image/jpeg', 'image/pjpeg'),
            'jpg'	=>	array('image/jpeg', 'image/pjpeg'),
            'jpe'	=>	array('image/jpeg', 'image/pjpeg'),
            'jp2'	=>	array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
            'j2k'	=>	array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
            'jpf'	=>	array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
            'jpg2'	=>	array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
            'jpx'	=>	array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
            'jpm'	=>	array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
            'mj2'	=>	array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
            'mjp2'	=>	array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
            'png'	=>	array('image/png',  'image/x-png'),
            'tiff'	=>	'image/tiff',
            'tif'	=>	'image/tiff',
            'css'	=>	array('text/css', 'text/plain'),
            'html'	=>	array('text/html', 'text/plain'),
            'htm'	=>	array('text/html', 'text/plain'),
            'shtml'	=>	array('text/html', 'text/plain'),
            'txt'	=>	'text/plain',
            'text'	=>	'text/plain',
            'log'	=>	array('text/plain', 'text/x-log'),
            'rtx'	=>	'text/richtext',
            'rtf'	=>	'text/rtf',
            'xml'	=>	array('resource/xml', 'text/xml', 'text/plain'),
            'xsl'	=>	array('resource/xml', 'text/xsl', 'text/xml'),
            'mpeg'	=>	'video/mpeg',
            'mpg'	=>	'video/mpeg',
            'mpe'	=>	'video/mpeg',
            'qt'	=>	'video/quicktime',
            'mov'	=>	'video/quicktime',
            'avi'	=>	array('video/x-msvideo', 'video/msvideo', 'video/avi', 'resource/x-troff-msvideo'),
            'movie'	=>	'video/x-sgi-movie',
            'doc'	=>	array('resource/msword', 'resource/vnd.ms-office'),
            'docx'	=>	array('resource/vnd.openxmlformats-officedocument.wordprocessingml.document', 'resource/zip', 'resource/msword', 'resource/x-zip'),
            'dot'	=>	array('resource/msword', 'resource/vnd.ms-office'),
            'dotx'	=>	array('resource/vnd.openxmlformats-officedocument.wordprocessingml.document', 'resource/zip', 'resource/msword'),
            'xlsx'	=>	array('resource/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'resource/zip', 'resource/vnd.ms-excel', 'resource/msword', 'resource/x-zip'),
            'word'	=>	array('resource/msword', 'resource/octet-stream'),
            'xl'	=>	'resource/excel',
            'eml'	=>	'message/rfc822',
            'json'  =>	array('resource/json', 'text/json'),
            'pem'   =>	array('resource/x-x509-user-cert', 'resource/x-pem-file', 'resource/octet-stream'),
            'p10'   =>	array('resource/x-pkcs10', 'resource/pkcs10'),
            'p12'   =>	'resource/x-pkcs12',
            'p7a'   =>	'resource/x-pkcs7-signature',
            'p7c'   =>	array('resource/pkcs7-mime', 'resource/x-pkcs7-mime'),
            'p7m'   =>	array('resource/pkcs7-mime', 'resource/x-pkcs7-mime'),
            'p7r'   =>	'resource/x-pkcs7-certreqresp',
            'p7s'   =>	'resource/pkcs7-signature',
            'crt'   =>	array('resource/x-x509-ca-cert', 'resource/x-x509-user-cert', 'resource/pkix-cert'),
            'crl'   =>	array('resource/pkix-crl', 'resource/pkcs-crl'),
            'der'   =>	'resource/x-x509-ca-cert',
            'kdb'   =>	'resource/octet-stream',
            'pgp'   =>	'resource/pgp',
            'gpg'   =>	'resource/gpg-keys',
            'sst'   =>	'resource/octet-stream',
            'csr'   =>	'resource/octet-stream',
            'rsa'   =>	'resource/x-pkcs7',
            'cer'   =>	array('resource/pkix-cert', 'resource/x-x509-ca-cert'),
            '3g2'   =>	'video/3gpp2',
            '3gp'   =>	array('video/3gp', 'video/3gpp'),
            'mp4'   =>	'video/mp4',
            'm4a'   =>	'audio/x-m4a',
            'f4v'   =>	array('video/mp4', 'video/x-f4v'),
            'flv'	=>	'video/x-flv',
            'webm'	=>	'video/webm',
            'aac'   =>	'audio/x-acc',
            'm4u'   =>	'resource/vnd.mpegurl',
            'm3u'   =>	'text/plain',
            'xspf'  =>	'resource/xspf+xml',
            'vlc'   =>	'resource/videolan',
            'wmv'   =>	array('video/x-ms-wmv', 'video/x-ms-asf'),
            'au'    =>	'audio/x-au',
            'ac3'   =>	'audio/ac3',
            'flac'  =>	'audio/x-flac',
            'ogg'   =>	array('audio/ogg', 'video/ogg', 'resource/ogg'),
            'kmz'	=>	array('resource/vnd.google-earth.kmz', 'resource/zip', 'resource/x-zip'),
            'kml'	=>	array('resource/vnd.google-earth.kml+xml', 'resource/xml', 'text/xml'),
            'ics'	=>	'text/calendar',
            'ical'	=>	'text/calendar',
            'zsh'	=>	'text/x-scriptzsh',
            '7zip'	=>	array('resource/x-compressed', 'resource/x-zip-compressed', 'resource/zip', 'multipart/x-zip'),
            'cdr'	=>	array('resource/cdr', 'resource/coreldraw', 'resource/x-cdr', 'resource/x-coreldraw', 'image/cdr', 'image/x-cdr', 'zz-resource/zz-winassoc-cdr'),
            'wma'	=>	array('audio/x-ms-wma', 'video/x-ms-asf'),
            'jar'	=>	array('resource/java-archive', 'resource/x-java-resource', 'resource/x-jar', 'resource/x-compressed'),
            'svg'	=>	array('image/svg+xml', 'resource/xml', 'text/xml'),
            'vcf'	=>	'text/x-vcard',
            'srt'	=>	array('text/srt', 'text/plain'),
            'vtt'	=>	array('text/vtt', 'text/plain'),
            'ico'	=>	array('image/x-icon', 'image/x-ico', 'image/vnd.microsoft.icon')
        ),
        'smiley' => array(
            //	smiley			image name						width	height	alt
            ':-)'			=>	array('grin.gif',			'19',	'19',	'grin'),
            ':lol:'			=>	array('lol.gif',			'19',	'19',	'LOL'),
            ':cheese:'		=>	array('cheese.gif',			'19',	'19',	'cheese'),
            ':)'			=>	array('smile.gif',			'19',	'19',	'smile'),
            ';-)'			=>	array('wink.gif',			'19',	'19',	'wink'),
            ';)'			=>	array('wink.gif',			'19',	'19',	'wink'),
            ':smirk:'		=>	array('smirk.gif',			'19',	'19',	'smirk'),
            ':roll:'		=>	array('rolleyes.gif',		'19',	'19',	'rolleyes'),
            ':-S'			=>	array('confused.gif',		'19',	'19',	'confused'),
            ':wow:'			=>	array('surprise.gif',		'19',	'19',	'surprised'),
            ':bug:'			=>	array('bigsurprise.gif',	'19',	'19',	'big surprise'),
            ':-P'			=>	array('tongue_laugh.gif',	'19',	'19',	'tongue laugh'),
            '%-P'			=>	array('tongue_rolleye.gif',	'19',	'19',	'tongue rolleye'),
            ';-P'			=>	array('tongue_wink.gif',	'19',	'19',	'tongue wink'),
            ':P'			=>	array('raspberry.gif',		'19',	'19',	'raspberry'),
            ':blank:'		=>	array('blank.gif',			'19',	'19',	'blank stare'),
            ':long:'		=>	array('longface.gif',		'19',	'19',	'long face'),
            ':ohh:'			=>	array('ohh.gif',			'19',	'19',	'ohh'),
            ':grrr:'		=>	array('grrr.gif',			'19',	'19',	'grrr'),
            ':gulp:'		=>	array('gulp.gif',			'19',	'19',	'gulp'),
            '8-/'			=>	array('ohoh.gif',			'19',	'19',	'oh oh'),
            ':down:'		=>	array('downer.gif',			'19',	'19',	'downer'),
            ':red:'			=>	array('embarrassed.gif',	'19',	'19',	'red face'),
            ':sick:'		=>	array('sick.gif',			'19',	'19',	'sick'),
            ':shut:'		=>	array('shuteye.gif',		'19',	'19',	'shut eye'),
            ':-/'			=>	array('hmm.gif',			'19',	'19',	'hmmm'),
            '>:('			=>	array('mad.gif',			'19',	'19',	'mad'),
            ':mad:'			=>	array('mad.gif',			'19',	'19',	'mad'),
            '>:-('			=>	array('angry.gif',			'19',	'19',	'angry'),
            ':angry:'		=>	array('angry.gif',			'19',	'19',	'angry'),
            ':zip:'			=>	array('zip.gif',			'19',	'19',	'zipper'),
            ':kiss:'		=>	array('kiss.gif',			'19',	'19',	'kiss'),
            ':ahhh:'		=>	array('shock.gif',			'19',	'19',	'shock'),
            ':coolsmile:'	=>	array('shade_smile.gif',	'19',	'19',	'cool smile'),
            ':coolsmirk:'	=>	array('shade_smirk.gif',	'19',	'19',	'cool smirk'),
            ':coolgrin:'	=>	array('shade_grin.gif',		'19',	'19',	'cool grin'),
            ':coolhmm:'		=>	array('shade_hmm.gif',		'19',	'19',	'cool hmm'),
            ':coolmad:'		=>	array('shade_mad.gif',		'19',	'19',	'cool mad'),
            ':coolcheese:'	=>	array('shade_cheese.gif',	'19',	'19',	'cool cheese'),
            ':vampire:'		=>	array('vampire.gif',		'19',	'19',	'vampire'),
            ':snake:'		=>	array('snake.gif',			'19',	'19',	'snake'),
            ':exclaim:'		=>	array('exclaim.gif',		'19',	'19',	'exclaim'),
            ':question:'	=>	array('question.gif',		'19',	'19',	'question')
        ),
        'user_agent' => array(
            'mobile' => array(
                // legacy array, old values commented out
                'mobileexplorer'	=> 'Mobile Explorer',
                //  'openwave'			=> 'Open Wave',
                //	'opera mini'		=> 'Opera Mini',
                //	'operamini'			=> 'Opera Mini',
                //	'elaine'			=> 'Palm',
                'palmsource'		=> 'Palm',
                //	'digital paths'		=> 'Palm',
                //	'avantgo'			=> 'Avantgo',
                //	'xiino'				=> 'Xiino',
                'palmscape'			=> 'Palmscape',
                //	'nokia'				=> 'Nokia',
                //	'ericsson'			=> 'Ericsson',
                //	'blackberry'		=> 'BlackBerry',
                //	'motorola'			=> 'Motorola'

                // Phones and Manufacturers
                'motorola'		=> 'Motorola',
                'nokia'			=> 'Nokia',
                'palm'			=> 'Palm',
                'iphone'		=> 'Apple iPhone',
                'ipad'			=> 'iPad',
                'ipod'			=> 'Apple iPod Touch',
                'sony'			=> 'Sony Ericsson',
                'ericsson'		=> 'Sony Ericsson',
                'blackberry'	=> 'BlackBerry',
                'cocoon'		=> 'O2 Cocoon',
                'blazer'		=> 'Treo',
                'lg'			=> 'LG',
                'amoi'			=> 'Amoi',
                'xda'			=> 'XDA',
                'mda'			=> 'MDA',
                'vario'			=> 'Vario',
                'htc'			=> 'HTC',
                'samsung'		=> 'Samsung',
                'sharp'			=> 'Sharp',
                'sie-'			=> 'Siemens',
                'alcatel'		=> 'Alcatel',
                'benq'			=> 'BenQ',
                'ipaq'			=> 'HP iPaq',
                'mot-'			=> 'Motorola',
                'playstation portable'	=> 'PlayStation Portable',
                'playstation 3'		=> 'PlayStation 3',
                'playstation vita'  	=> 'PlayStation Vita',
                'hiptop'		=> 'Danger Hiptop',
                'nec-'			=> 'NEC',
                'panasonic'		=> 'Panasonic',
                'philips'		=> 'Philips',
                'sagem'			=> 'Sagem',
                'sanyo'			=> 'Sanyo',
                'spv'			=> 'SPV',
                'zte'			=> 'ZTE',
                'sendo'			=> 'Sendo',
                'nintendo dsi'	=> 'Nintendo DSi',
                'nintendo ds'	=> 'Nintendo DS',
                'nintendo 3ds'	=> 'Nintendo 3DS',
                'wii'			=> 'Nintendo Wii',
                'open web'		=> 'Open Web',
                'openweb'		=> 'OpenWeb',

                // Operating Systems
                'android'		=> 'Android',
                'symbian'		=> 'Symbian',
                'SymbianOS'		=> 'SymbianOS',
                'elaine'		=> 'Palm',
                'series60'		=> 'Symbian S60',
                'windows ce'	=> 'Windows CE',

                // Browsers
                'obigo'			=> 'Obigo',
                'netfront'		=> 'Netfront Browser',
                'openwave'		=> 'Openwave Browser',
                'mobilexplorer'	=> 'Mobile Explorer',
                'operamini'		=> 'Opera Mini',
                'opera mini'	=> 'Opera Mini',
                'opera mobi'	=> 'Opera Mobile',
                'fennec'		=> 'Firefox Mobile',

                // Other
                'digital paths'	=> 'Digital Paths',
                'avantgo'		=> 'AvantGo',
                'xiino'			=> 'Xiino',
                'novarra'		=> 'Novarra Transcoder',
                'vodafone'		=> 'Vodafone',
                'docomo'		=> 'NTT DoCoMo',
                'o2'			=> 'O2',

                // Fallback
                'mobile'		=> 'Generic Mobile',
                'wireless'		=> 'Generic Mobile',
                'j2me'			=> 'Generic Mobile',
                'midp'			=> 'Generic Mobile',
                'cldc'			=> 'Generic Mobile',
                'up.link'		=> 'Generic Mobile',
                'up.browser'	=> 'Generic Mobile',
                'smartphone'	=> 'Generic Mobile',
                'cellphone'		=> 'Generic Mobile'
            ),
            'robot' => array(
                'googlebot'		=> 'Googlebot',
                'msnbot'		=> 'MSNBot',
                'baiduspider'		=> 'Baiduspider',
                'bingbot'		=> 'Bing',
                'slurp'			=> 'Inktomi Slurp',
                'yahoo'			=> 'Yahoo',
                'ask jeeves'		=> 'Ask Jeeves',
                'fastcrawler'		=> 'FastCrawler',
                'infoseek'		=> 'InfoSeek Robot 1.0',
                'lycos'			=> 'Lycos',
                'yandex'		=> 'YandexBot',
                'mediapartners-google'	=> 'MediaPartners Google',
                'CRAZYWEBCRAWLER'	=> 'Crazy Webcrawler',
                'adsbot-google'		=> 'AdsBot Google',
                'feedfetcher-google'	=> 'Feedfetcher Google',
                'curious george'	=> 'Curious George'
            ),
            'browser' => array(
                'OPR'			=> 'Opera',
                'Flock'			=> 'Flock',
                'Edge'			=> 'Spartan',
                'Chrome'		=> 'Chrome',
                // Opera 10+ always reports Opera/9.80 and appends Version/<real version> to the user agent string
                'Opera.*?Version'	=> 'Opera',
                'Opera'			=> 'Opera',
                'MSIE'			=> 'Internet Explorer',
                'Internet Explorer'	=> 'Internet Explorer',
                'Trident.* rv'	=> 'Internet Explorer',
                'Shiira'		=> 'Shiira',
                'Firefox'		=> 'Firefox',
                'Chimera'		=> 'Chimera',
                'Phoenix'		=> 'Phoenix',
                'Firebird'		=> 'Firebird',
                'Camino'		=> 'Camino',
                'Netscape'		=> 'Netscape',
                'OmniWeb'		=> 'OmniWeb',
                'Safari'		=> 'Safari',
                'Mozilla'		=> 'Mozilla',
                'Konqueror'		=> 'Konqueror',
                'icab'			=> 'iCab',
                'Lynx'			=> 'Lynx',
                'Links'			=> 'Links',
                'hotjava'		=> 'HotJava',
                'amaya'			=> 'Amaya',
                'IBrowse'		=> 'IBrowse',
                'Maxthon'		=> 'Maxthon',
                'Ubuntu'		=> 'Ubuntu Web Browser'
            ),
            'platform' => array(
                'windows nt 10.0'	=> 'Windows 10',
                'windows nt 6.3'	=> 'Windows 8.1',
                'windows nt 6.2'	=> 'Windows 8',
                'windows nt 6.1'	=> 'Windows 7',
                'windows nt 6.0'	=> 'Windows Vista',
                'windows nt 5.2'	=> 'Windows 2003',
                'windows nt 5.1'	=> 'Windows XP',
                'windows nt 5.0'	=> 'Windows 2000',
                'windows nt 4.0'	=> 'Windows NT 4.0',
                'winnt4.0'			=> 'Windows NT 4.0',
                'winnt 4.0'			=> 'Windows NT',
                'winnt'				=> 'Windows NT',
                'windows 98'		=> 'Windows 98',
                'win98'				=> 'Windows 98',
                'windows 95'		=> 'Windows 95',
                'win95'				=> 'Windows 95',
                'windows phone'			=> 'Windows Phone',
                'windows'			=> 'Unknown Windows OS',
                'android'			=> 'Android',
                'blackberry'		=> 'BlackBerry',
                'iphone'			=> 'iOS',
                'ipad'				=> 'iOS',
                'ipod'				=> 'iOS',
                'os x'				=> 'Mac OS X',
                'ppc mac'			=> 'Power PC Mac',
                'freebsd'			=> 'FreeBSD',
                'ppc'				=> 'Macintosh',
                'linux'				=> 'Linux',
                'debian'			=> 'Debian',
                'sunos'				=> 'Sun Solaris',
                'beos'				=> 'BeOS',
                'apachebench'		=> 'ApacheBench',
                'aix'				=> 'AIX',
                'irix'				=> 'Irix',
                'osf'				=> 'DEC OSF',
                'hp-ux'				=> 'HP-UX',
                'netbsd'			=> 'NetBSD',
                'bsdi'				=> 'BSDi',
                'openbsd'			=> 'OpenBSD',
                'gnu'				=> 'GNU/Linux',
                'unix'				=> 'Unknown Unix OS',
                'symbian' 			=> 'Symbian OS'
            ),
        ),
    );

    /**
     * Realconfig
     *
     * @var array
     */
    protected static $config = array(
        'app' => array(),
        'db' => array(),
        'memcache' => array(),
        'doctype' => array(),
        'foreign_char' => array(),
        'mime_type' => array(),
        'smiley' => array(),
        'user_agent' => array(
            'mobile' => array(),
            'robot' => array(),
            'browser' => array(),
            'platform' => array(),
        ),
    );
    /**
     * Object instance
     *
     * @var Processor
     */
    private static $instance;
    /**
     * detect if has run
     *
     * @var boolean
     */
    private static $hasRun = false;

    /**
     * Proccessor constructor.
     */
    private function __construct()
    {
        self::$instance = $this;
        self::$config['app'] = $this->config_default['app'];
        self::$environment = self::$config['app']['environment'];
        self::$config['db'] = $this->config_default['db'];
//        self::$config['memcache'] = $this->config_default['memcache'];
//        self::$config['doctype'] = $this->config_default['doctype'];
//        self::$config['foreign_char'] = $this->config_default['foreign_char'];
//        self::$config['mime_type'] = $this->config_default['mime_type'];
        self::$config['smiley'] = $this->config_default['smiley'];
    }

    /**
     * Get config
     *
     * @param string|null $type
     * @param string|null $key
     * @return array|null
     */
    public static function &config($type = null, $key = null)
    {
        $retval = null;
        $instance = self::instance(); // init
        if ($type === null) {
            return $instance::$config;
        }
        if (is_string($type) && isset(self::$config)) {
            if ($key === null) {
                return self::$config[$type];
            } elseif (is_string($key) && array_key_exists($key, self::$config[$type])) {
                return self::$config[$type][$key];
            }
        }
        return $retval;
    }

    /**
     * @param array $replace
     * @return array
     */
    public function setAppConfig(array $replace)
    {
        foreach ($replace as $key => $value) {
            // dont allow change subclass_prefix
            if ($key == 'subclass_prefix') {
                continue;
            }
            if ($key == 'composer_autoload' && !is_string($value) && ! file_exists($value)) {
                continue;
            }
            self::$config['app'][$key] = $value;
        }

        return self::$config['app'];
    }

    /**
     * @return Processor
     */
    public static function instance()
    {
        (!is_object(self::$instance)) && new self();
        return self::$instance;
    }

    /**
     * @return Processor
     */
    public static function run()
    {
        /* ------------------------------------------------------
        *  Load the global functions
        * ------------------------------------------------------
        */
        $instance = self::instance();
        if (self::$hasRun) {
            // error
            show_error('Only allowed run resource once');
        }
        self::$hasRun = true;
        return $instance
            ->init()
            ->registerError()
            ->checkConfig()
            ->registerConfig()
            ->autoloadComposer()
            ->startProccess();
    }

    /**
     * Initial
     *
     * @return Processor
     */
    private function init()
    {
        if (! is_php('5.4')) {
            show_error(
                sprintf(
                    'Minimum PHP Requirements is 5.4 you current version is : %s',
                    PHP_VERSION
                ),
                503
            );
        }
        $ext = array(
            'curl',
            'mcrypt',
            'mbstring',
            'ctype',
            'iconv',
            'json',
            'pcre',
        );

        array_map(function($c) {
            if (!extension_loaded($c)) {
                show_error(
                    sprintf(
                        '[ <strong> %s </strong>] extension must be enabled to use this application',
                        $c
                    ),
                    503
                );
            }
        }, $ext);

        return $this;
    }

    private function registerError()
    {
        /*
         * ------------------------------------------------------
         *  Define a custom error handler so we can log PHP errors
         * ------------------------------------------------------
         */
        set_error_handler('_error_handler');
        set_exception_handler('_exception_handler');
        register_shutdown_function('_shutdown_handler');

        return $this;
    }

    private function checkConfig()
    {
        if (!is_file(CONFIG)) {
            show_error(
                'Configuration file does not exist',
                503
            );
        }

        return $this;
    }

    private function registerConfig()
    {
        /** @noinspection PhpIncludeInspection */
        $config = require(CONFIG);
        if (empty($config)) {
            show_error('Configuration file is empty');
        }
        if (!is_array($config)) {
            show_error('Invalid config file, configuration file must be array');
        }
        if (!isset($config['app'])) {
            show_error('Empty config for resource, configuration for \'app\' must be as array');
        }
        if (! is_array($config['app'])) {
            show_error('Invalid config for resource, configuration for \'app\' must be as array');
        }
        if (!empty($config['app']['environment']) && is_string($config['app']['environment'])) {
            self::$environment = $config['app']['environment'];
        }

        // set the environment
        $this->setEnvironment();

        if (empty($config['user_agent']['mobile']) || !is_array($config['user_agent']['mobile'])) {
            $config['user_agent']['mobile'] = $this->config_default['user_agent']['mobile'];
        }
        if (empty($config['user_agent']['robot']) || !is_array($config['user_agent']['robot'])) {
            $config['user_agent']['robot'] = $this->config_default['user_agent']['robot'];
        }
        if (empty($config['user_agent']['platform']) || !is_array($config['user_agent']['platform'])) {
            $config['user_agent']['platform'] = $this->config_default['user_agent']['platform'];
        }
        if (empty($config['user_agent']['browser']) || !is_array($config['user_agent']['browser'])) {
            $config['user_agent']['browser'] = $this->config_default['user_agent']['browser'];
        }

        $config_new = $config;
        if (is_dir(CONFIGPATH)) {
            $env_config = array();
            if (file_exists(CONFIGPATH . ENVIRONMENT . DIRECTORY_SEPARATOR . 'config.php')) {
                /** @noinspection PhpIncludeInspection */
                $env_config = require(CONFIGPATH . ENVIRONMENT . DIRECTORY_SEPARATOR . 'config.php');
                if (!empty($env_config) && is_array($env_config)) {
                    $config = array_merge($config, $env_config);
                    unset($config['path'], $config['app']['environment']);
                }
            }
            if (file_exists(CONFIGPATH . ENVIRONMENT . DIRECTORY_SEPARATOR . 'mimes.php')) {
                /** @noinspection PhpIncludeInspection */
                $env_config = require(CONFIGPATH . ENVIRONMENT . DIRECTORY_SEPARATOR . 'mime_type.php');
                if (!empty($env_config) && is_array($env_config)) {
                    if (!isset($config['mime_type']) || !is_array($config['mime_type'])) {
                        $config['mime_type'] = $this->config_default['mime_type'];
                    }
                    $config['mime_type'] = array_merge($config['mime_type'], $env_config);
                }
            }
            if (file_exists(CONFIGPATH . ENVIRONMENT . DIRECTORY_SEPARATOR . 'doctype.php')) {
                /** @noinspection PhpIncludeInspection */
                $env_config = require(CONFIGPATH . ENVIRONMENT . DIRECTORY_SEPARATOR . 'doctype.php');
                if (!empty($env_config) && is_array($env_config)) {
                    if (!isset($config['doctype']) || !is_array($config['doctype'])) {
                        $config['doctype'] = $this->config_default['doctype'];
                    }
                    $config['doctype'] = array_merge($config['doctype'], $env_config);
                }
            }
            if (file_exists(CONFIGPATH . ENVIRONMENT . DIRECTORY_SEPARATOR . 'user_agent.php')) {
                /** @noinspection PhpIncludeInspection */
                $env_config = require(CONFIGPATH . ENVIRONMENT . DIRECTORY_SEPARATOR . 'user_agent.php');
                if (!empty($env_config) && is_array($env_config) &&
                    (! empty($env_config['mobile']) && is_array($env_config['mobile'])
                    || empty($env_config['browser']) && is_array($env_config['browser'])
                    || empty($env_config['robot']) && is_array($env_config['robot'])
                    || empty($env_config['platform']) && is_array($env_config['platform'])
                    )
                ) {
                    $config['user_agent']['mobile'] = array_merge(
                        $config['user_agent'],
                        (
                            !empty($env_config['mobile']) && is_array($env_config['mobile']) ? $env_config['mobile'] : array()
                        )
                    );
                    $config['user_agent']['robot'] = array_merge(
                        $config['user_agent'],
                        (
                            !empty($env_config['robot']) && is_array($env_config['robot']) ? $env_config['robot'] : array()
                        )
                    );
                    $config['user_agent']['platform'] = array_merge(
                        $config['user_agent'],
                        (
                            !empty($env_config['platform']) && is_array($env_config['platform']) ? $env_config['platform'] : array()
                        )
                    );
                    $config['user_agent']['browser'] = array_merge(
                        $config['user_agent'],
                        (
                            !empty($env_config['browser']) && is_array($env_config['browser']) ? $env_config['browser'] : array()
                        )
                    );
                }
            }

            $config_new = array_merge($config_new, $config);
        }

        unset($config, $env_config);

        if (empty($config_new['db'])) {
            show_error('Empty config for database connection, configuration for \'db\' must be as array and could not to be empty.');
        }

        if (!is_array($config_new['db'])) {
            show_error('Invalid config for resource, configuration for \'db\' must be as array');
        }

        $config_new['app']['environment'] = ENVIRONMENT;
        $this->setAppConfig($config_new['app']);
        self::$config['db'] = array_merge(self::$config['db'], $config_new['db']);

        if (!empty($config_new['mime_type']) && is_array($config_new['mime_type'])) {
            self::$config['mime_type'] = array_merge($this->config_default['mime_type'], $config_new['mime_type']);
        }

        if (!empty($config_new['memcache']) && is_array($config_new['memcache'])) {
            self::$config['memcache'] = $config_new['memcache'];
        }

        if (!empty($config_new['foreign_char']) && is_array($config_new['foreign_char'])) {
            self::$config['foreign_char'] = array_merge($this->config_default['foreign_char'], $config_new['foreign_char']);
        }

        if (!empty($config_new['doctype']) && is_array($config_new['doctype'])) {
            self::$config['doctype'] = array_merge($this->config_default['doctype'], $config_new['doctype']);
        }

        unset($config_new['doctype'],
            $config_new['foreign_char'],
            $config_new['memcache'],
            $config_new['mime_type'],
            $config_new['db'],
            $config_new['smiley'],
            $config_new['app'],
            $config_new['user_agent']
        );
        // set additional values
        foreach ($config_new as $key => $value) {
            self::$config[$key] = $value;
        }
        unset($config_new);
        return $this;
    }

    private function autoloadComposer()
    {
        $composer_autoload = config_item('composer_autoload');
        /*
         * ------------------------------------------------------
         *  Should we use a Composer autoloader?
         * ------------------------------------------------------
         */
        if (is_string($composer_autoload) && file_exists($composer_autoload)) {
            require_once($composer_autoload);
        } elseif (file_exists(SOURCEPATH.'vendor/autoload.php')) {
            /** @noinspection PhpIncludeInspection */
            require_once(SOURCEPATH.'vendor/autoload.php');
        } elseif (file_exists(FCPATH . 'vendor/autoload.php')) {
            /** @noinspection PhpIncludeInspection */
            require_once(FCPATH.'vendor/autoload.php');
        } else {
            show_error(
                'autoload.php on vendor directory does not exist. You must install necessary dependency from composer.',
                503
            );
        }

        $class_mustBeExist = array(
            '\\Aufa\\Encryption\\Encryption',
            '\\Gettext\\GettextTranslator',
            '\\Pentagonal\\Hookable\\Hookable',
            '\\Pentagonal\\Phpass\\PasswordHash',
            '\\Pentagonal\\StaticHelper\\FilterHelper',
            '\\Pentagonal\\StaticHelper\\InternalHelper',
            '\\Pentagonal\\StaticHelper\\PathHelper',
            '\\Pentagonal\\StaticHelper\\StringHelper',
        );
        array_map(function ($c) {
            if (!class_exists($c)) {
                show_error(
                    sprintf(
                        'Class [ <strong> %s </strong> ] does not exist. Please verify the installation.',
                        ltrim($c, '\\')
                    ),
                    503
                );
            }
        }, $class_mustBeExist);
        return $this;
    }
    private function setEnvironment()
    {
        $allowed_environment = array(
            'development',
            'dev',
            'testing',
            'test',
            'production',
            'prod',
        );

        $env = self::$environment;
        if (!is_string($env) || !trim(trim(strtolower($env)))
            || !in_array(trim(strtolower($env)), $allowed_environment)
        ) {
            $env = 'production';
        } else {
            $env = strtolower(trim($env));
            $env = $env[0];
            if ($env == 'd') {
                $env = 'development';
            } elseif ($env == 't') {
                $env = 'testing';
            } else {
                $env = 'production';
            }
        }
        !defined('ENVIRONMENT') && define('ENVIRONMENT', $env);
        /*
        |--------------------------------------------------------------------------
        | Environment
        |--------------------------------------------------------------------------
        */
        if (ENVIRONMENT == 'development') {
            error_reporting(-1);
            ini_set('display_errors', 1);
        } else {
            ini_set('display_errors', 0);
            $error_reporting = version_compare(PHP_VERSION, '5.3', '>=')
                ? E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED
                : E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE;
            error_reporting($error_reporting);
            unset($error_reporting);
        }

        return $this;
    }

    /**
     * @return Processor
     */
    private function startProccess()
    {
        /*
         * ------------------------------------------------------
         *  Start the timer... tick tock tick tock...
         * ------------------------------------------------------
         */
        $benchmark_class =& load_class('Benchmark', 'core');
        $benchmark_class->mark('total_execution_time_start');
        $benchmark_class->mark('loading_time:_base_classes_start');

        /*
         * ------------------------------------------------------
         *  Instantiate the hooks class
         * ------------------------------------------------------
         */
        $hooks_class =& load_class('Hooks', 'core');

        /*
         * ------------------------------------------------------
         *  Is there a "pre_system" hook?
         * ------------------------------------------------------
         */
        $hooks_class->call_hook('pre_system');
        /*
         * ------------------------------------------------------
         *  Instantiate the config class
         * ------------------------------------------------------
         *
         * Note: It is important that Config is loaded first as
         * most other classes depend on it either directly or by
         * depending on another class that uses it.
         *
         */
        $cfg =& load_class('Config', 'core');

        /*
         * ------------------------------------------------------
         * Important charset-related stuff
         * ------------------------------------------------------
         *
         * Configure mbstring and/or iconv if they are enabled
         * and set MB_ENABLED and ICONV_ENABLED constants, so
         * that we don't repeatedly do extension_loaded() or
         * function_exists() calls.
         *
         * Note: UTF-8 class depends on this. It used to be done
         * in it's constructor, but it's _not_ class-specific.
         *
         */
        $charset = strtoupper(config_item('charset'));
        ini_set('default_charset', $charset);
        if (extension_loaded('mbstring')) {
            define('MB_ENABLED', true);
            // mbstring.internal_encoding is deprecated starting with PHP 5.6
            // and it's usage triggers E_DEPRECATED messages.
            @ini_set('mbstring.internal_encoding', $charset);
            // This is required for mb_convert_encoding() to strip invalid characters.
            // That's utilized by CI_Utf8, but it's also done for consistency with iconv.
            mb_substitute_character('none');
        } else  {
            define('MB_ENABLED', false);
        }

        /*
         * ------------------------------------------------------
         * There's an ICONV_IMPL constant, but the PHP manual says that using
         * iconv's predefined constants is "strongly discouraged".
         * ------------------------------------------------------
         */
        if (extension_loaded('iconv')) {
            define('ICONV_ENABLED', true);
            // iconv.internal_encoding is deprecated starting with PHP 5.6
            // and it's usage triggers E_DEPRECATED messages.
            @ini_set('iconv.internal_encoding', $charset);
        } else {
            define('ICONV_ENABLED', false);
        }

        if (is_php('5.6')) {
            ini_set('php.internal_encoding', $charset);
        }

        /*
         * ------------------------------------------------------
         *  Load compatibility features
         * ------------------------------------------------------
         * using array map to prevent accessed global directly
         */
        array_map(function ($c) {
                /** @noinspection PhpIncludeInspection */
                require_once BASEPATH . 'core' . DIRECTORY_SEPARATOR. 'compat' . DIRECTORY_SEPARATOR . $c;
            },
            array(
                'mbstring.php',
                'hash.php',
                'password.php',
                'standard.php',
            )
        );

        /*
         * ------------------------------------------------------
         *  Instantiate the UTF-8 class
         * ------------------------------------------------------
         */
        load_class('Utf8', 'core');
        /*
         * ------------------------------------------------------
         *  Instantiate the URI class
         * ------------------------------------------------------
         */
        $uri_class =& load_class('URI', 'core');

        /*
         * ------------------------------------------------------
         *  Instantiate the routing class and set the routing
         * ------------------------------------------------------
         */
        $router_class =& load_class('Router', 'core');
        /*
         * ------------------------------------------------------
         *  Instantiate the output class
         * ------------------------------------------------------
         */
        $out =& load_class('Output', 'core');
        /*
         * ------------------------------------------------------
         *	Is there a valid cache file? If so, we're done...
         * ------------------------------------------------------
         */
        if ($hooks_class->call_hook('cache_override') === false && $out->_display_cache($cfg, $uri_class) === true) {
            exit(0);
        }

        /*
         * -----------------------------------------------------
         * Load the security class for xss and csrf support
         * & Input class and sanitize globals
         * & Load the Language class
         * -----------------------------------------------------
         */
        load_class('Security', 'core');
        load_class('Input', 'core');
        load_class('Lang', 'core');

        /**
         * Create Instance
         */
        $this->createInstance();

        // Set a mark point for benchmarking
        $benchmark_class->mark('loading_time:_base_classes_end');
        /*
         * ------------------------------------------------------
         *  Sanity checks
         * ------------------------------------------------------
         *
         *  The Router class has already validated the request,
         *  leaving us with 3 options here:
         *
         *	1) an empty class name, if we reached the default
         *	   controller, but it didn't exist;
         *	2) a query string which doesn't go through a
         *	   file_exists() check
         *	3) a regular request for a non-existing page
         *
         *  We handle all of these as a 404 error.
         *
         *  Furthermore, none of the methods in the app controller
         *  or the loader class can be called via the URI, nor can
         *  controller methods that begin with an underscore.
         */

        $e404 = false;
        $class = ucfirst($router_class->class);
        $method = $router_class->method;

        if (empty($class) || ! file_exists(CONTROLLERPATH . $router_class->directory.$class.'.php')) {
            $e404 = true;
        } else {
            /** @noinspection PhpIncludeInspection */
            require_once(SOURCEPATH.'controller/'.$router_class->directory.$class.'.php');
            if ( ! class_exists($class, false) || $method[0] === '_' || method_exists('CI_Controller', $method)) {
                $e404 = true;
            } elseif (method_exists($class, '_remap')) {
                $params = array($method, array_slice($uri_class->rsegments, 2));
                $method = '_remap';
            } elseif (! in_array(strtolower($method), array_map('strtolower', get_class_methods($class)), true)) {
                // WARNING: It appears that there are issues with is_callable() even in PHP 5.2!
                // Furthermore, there are bug reports and feature/change requests related to it
                // that make it unreliable to use in this context. Please, DO NOT change this
                // work-around until a better alternative is available.
                $e404 = true;
            }
        }

        if ($e404) {
            if ( ! empty($router_class->routes['404_override'])) {
                $error_method = null;
                if (sscanf($router_class->routes['404_override'], '%[^/]/%s', $error_class, $error_method) !== 2) {
                    $error_method = 'index';
                }

                $error_class = ucfirst($error_class);
                if ( ! class_exists($error_class, false)) {
                    if (file_exists(CONTROLLERPATH . $router_class->directory.$error_class.'.php')) {
                        /** @noinspection PhpIncludeInspection */
                        require_once(CONTROLLERPATH . $router_class->directory.$error_class.'.php');
                        $e404 = ! class_exists($error_class, false);
                    } elseif (! empty($router_class->directory) && file_exists(APPPATH.'controllers/'.$error_class.'.php')) {
                        // Were we in a directory? If so, check for a global override
                        /** @noinspection PhpIncludeInspection */
                        require_once(CONTROLLERPATH . $error_class.'.php');
                        if (($e404 = ! class_exists($error_class, false)) === false) {
                            $router_class->directory = '';
                        }
                    }
                } else {
                    $e404 = false;
                }
            }

            // Did we reset the $e404 flag? If so, set the rsegments, starting from index 1
            if ( ! $e404) {
                if (!isset($error_class)) {
                    $error_class = null;
                }
                if (!isset($error_method)) {
                    $error_method = null;
                }
                $class = $error_class;
                $method = $error_method;

                $uri_class->rsegments = array(
                    1 => $class,
                    2 => $method
                );
            } else {
                show_404($router_class->directory.$class.'/'.$method);
            }
        }

        if ($method !== '_remap') {
            $params = array_slice($uri_class->rsegments, 2);
        }

        /*
         * ------------------------------------------------------
         *  Is there a "pre_controller" hook?
         * ------------------------------------------------------
         */
        $hooks_class->call_hook('pre_controller');

        /*
         * ------------------------------------------------------
         *  Instantiate the requested controller
         * ------------------------------------------------------
         */
        // Mark a start point so we can benchmark the controller
        $benchmark_class->mark('controller_execution_time_( '.$class.' / '.$method.' )_start');

        $codeIgniter = new $class();

        /*
         * ------------------------------------------------------
         *  Is there a "post_controller_constructor" hook?
         * ------------------------------------------------------
         */
        $hooks_class->call_hook('post_controller_constructor');

        /*
         * ------------------------------------------------------
         *  Call the requested method
         * ------------------------------------------------------
         */
        if (!isset($params)) {
            $params = array();
        }

        call_user_func_array(array(&$codeIgniter, $method), $params);

        // Mark a benchmark end point
        $benchmark_class->mark('controller_execution_time_( '.$class.' / '.$method.' )_end');

        /*
         * ------------------------------------------------------
         *  Is there a "post_controller" hook?
         * ------------------------------------------------------
         */
        $hooks_class->call_hook('post_controller');

        /*
         * ------------------------------------------------------
         *  Send the final rendered output to the browser
         * ------------------------------------------------------
         */
        if ($hooks_class->call_hook('display_override') === false) {
            $out->_display();
        }

        /*
         * ------------------------------------------------------
         *  Is there a "post_system" hook?
         * ------------------------------------------------------
         */
        $hooks_class->call_hook('post_system');

        return $this;
    }

    private function createInstance()
    {
        /**
         * ------------------------------------------------------
         *  Load the app controller and local controller
         * ------------------------------------------------------
         *
         */

        if (!file_exists(RESOURCEPATH . 'Core' . DIRECTORY_SEPARATOR . 'Controller.php')) {
            show_error('Class Pentagonal Controller Does Not Exists');
        }
        /** @noinspection PhpIncludeInspection */
        require_once RESOURCEPATH . 'Core' . DIRECTORY_SEPARATOR . 'Controller.php';
        /**
         * Reference to the CI_Controller method.
         *
         * Returns current CI instance object
         *
         * @return CI_Controller
         */
        function &get_instance()
        {
            return CI_Controller::get_instance();
        }

    }

    public function __destruct()
    {
        $this->config_default = array();
    }
}
