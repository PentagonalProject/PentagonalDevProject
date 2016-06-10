<?php
class AssetController extends CI_Controller
{
    /**
     * @var string
     */
    protected $mime_type = null;
    /**
     * @var string
     */
    protected $last_uri = null;
    /**
     * @var string
     */
    protected $url_asset = null;
    /**
     * @var array
     */
    protected $list_files = array();
    /**
     * @var string
     */
    protected $hash;
    /**
     * @var string
     */
    protected $mime;

    /**
     * Index
     */
    public function index()
    {
        /**
         * No error
         */
//        error_reporting(0);
//        ini_set('display_errors', 'off');
        // -------
        /** @noinspection PhpUndefinedFieldInspection */
        $this->mime_type = $this->uri->segment(2);
        $this->mime = $this->mime_type == 'js' ? 'application/javascript' : 'text/css';

        /** @noinspection PhpUndefinedFieldInspection */
        $this->last_uri = $this->uri->segment(3);
        /**
         * Length
         */
        $lengthlast  = strlen($this->mime_type)+2;
        $lenghthash  = 40;
        $lenghtfirst = 6;
        // get hash
        $this->hash = substr($this->last_uri, -($lengthlast + $lenghthash), $lenghthash);
        // url asset
        $this->url_asset = substr($this->last_uri, 0, - ($lengthlast + $lenghthash + $lenghtfirst));
        /**
         * Dummy
         */
        if ($this->url_asset == 'null' && sha1('null' . ENGINE_SALT) == $this->hash) {
            $this->generateEmpty();
        }

        $expl   = explode(',', $this->url_asset);
        $data = DynamicAsset::generate($expl, $this->mime_type);
        if (empty($data) || ! $data->checkIntegrity($this->hash)) {
            show_404();
            return;
        }

        if (empty($data->data)) {
            $this->generateEmpty();
        }
        $this->list_files = $data->data;
        $this->load->model('minify');
        $text = '';
        $method = $this->mime_type == 'css' ? 'cssFile' : 'jsFile';
        foreach ($this->list_files as $v) {
            if ($method == 'jsFile' && substr($v, -7) == '.min.js') {
                /** @noinspection PhpUndefinedFieldInspection */
                // doing minfy
                $text .= $this->minify->{$method}(ASSETPATH . $v, true, true, true) ."\n";
                continue;
            }
            /** @noinspection PhpUndefinedFieldInspection */
            // doing minify
            $text .= $this->minify->{$method}(ASSETPATH . $v) ."\n";
        }

        $text = trim($text);
        // if empty
        if ($text == '') {
            $this->generateEmpty();
        }
        $this->headerResponse(strlen($text));
        echo $text;
        unset($text);
        exit; // EOE
    }

    /**
     * Generate Null / Empty Asset
     */
    protected function generateEmpty()
    {
        $type_text = $this->mime_type == 'js' ? 'javascript' : 'stylesheet';
        $text = "/**\n * Empty {$type_text} \n */";
        $this->headerResponse(strlen($text));
        exit($text);
    }

    /**
     * @param integer $length
     */
    protected function headerResponse($length)
    {
        /**
         * Remove header X-Powered-By php output
         */
        if (!headers_sent()) {
            @header_remove('X-Powered-By');
        }

        $status = 200;
        /**
         * Check if got function apache_request_headers
         * @var array headers
         */
        $heads = function_exists('apache_request_headers')
            ? apache_request_headers()
            : headers_list();

        /**
         * Get max modified of time
         * @var array
         */
        $times = array();
        foreach ($this->list_files as $v) {
            if (file_exists(ASSETPATH . $v)) {
                $times[] = filemtime(ASSETPATH . $v);
            }
        }
        if (!empty($times)) {
            $date = gmdate('D, d M Y H:i:s', max($times)).' GMT';
            $etag = md5($date.$length);
        } else {
            $date = gmdate('D, d M Y H:i:s', time()).' GMT';
            $etag = md5($date.$length);
        }

        $header_list = [
            'Expires' => gmdate('D, d M Y H:i:s', (time()+3600)).' GMT',
            'Cache-Control' => 'max-age=3600',
            'Last-Modified' => $date,
        ];
        $has = false;
        if (!empty($heads['If-None-Match']) && !empty($heads['If-Modified-Since'])) {
            if ($etag == md5($heads['If-Modified-Since'].$length)) {
                $has = true;
                $status = 304;
                $header_list['ETag'] = $heads['If-None-Match']."\r\n";
                $header_list['Connection'] = 'close';
            }
        }

        /**
         * If none match
         */
        if (!$has) {
            $header_list['Cache-Control'] = 'max-age=3600';
            $header_list['ETag'] = $etag;
            $header_list['Content-Length'] = $length;
            $header_list['Accept-Ranges'] = 'bytes';
        }

        /**
         * set Status
         */
        set_status_header($status);
        header('Content-Type: '.$this->mime.';charset=utf-8');
        foreach ($header_list as $key => $header) {
            header("{$key}: {$header}", true);
        }
    }
}
