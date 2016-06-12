<?php
/**
 * @purpose Resize and saves image
 * @require PHP5, GD library.
 *
 * @author awan <nawa@yahoo.com>
 *
 */
final Class ImageResizer extends CI_Model
{
    protected $image_type_list = array(
        1 => array(
            'IMAGETYPE_GIF',
            'gif'
        ),
        2 => array(
            'IMAGETYPE_JPEG',
            'jpg'
        ),
        3 => array(
            'IMAGETYPE_PNG',
            'png'
        ),
        4 => array(
            'IMAGETYPE_SWF',
            'swf'
        ),
        5 => array(
            'IMAGETYPE_PSD',
            'psd'
        ),
        6 => array(
            'IMAGETYPE_BMP',
            'bmp'
        ),
        7 => array(
            'IMAGETYPE_TIFF_II',
            'tiff'
        ),
        8 => array(
            'IMAGETYPE_TIFF_MM',
            'tiff'
        ),
        9  => array(
            'IMAGETYPE_JPC',
            'jpc'
        ),
        10 => array(
            'IMAGETYPE_JP2',
            'jp2'
        ),
        11 => array(
            'IMAGETYPE_JPX',
            'jpx'
        ),
        12 => array(
            'IMAGETYPE_JB2',
            'jb2',
        ),
        13 => array(
            'IMAGETYPE_SWC',
            'swc'
        ),
        14 => array(
            'IMAGETYPE_IFF',
            'iff'
        ),
        15 => array(
            'IMAGETYPE_WBMP',
            'bmp'
        ),
        16 => array(
            'IMAGETYPE_XBM',
            'xbm'
        ),
        17 => array(
            'IMAGETYPE_ICO',
            'ico'
        ),
    );
    protected $image_type_function = array(
        'IMAGETYPE_GIF' => 'imagecreatefromgif',
        'IMAGETYPE_JPEG' => 'imagecreatefromjpeg',
        'IMAGETYPE_PNG' => 'imagecreatefrompng',
        'IMAGETYPE_SWF' => 'class:imagick',
        'IMAGETYPE_PSD' => 'class:imagick',
        'IMAGETYPE_BMP' => 'imagecreatefromwbmp',
        'IMAGETYPE_TIFF_II' => 'class:imagick',
        'IMAGETYPE_TIFF_MM' => 'class:imagick',
        'IMAGETYPE_JPC' => 'class:imagick',
        'IMAGETYPE_JP2' => 'class:imagick',
        'IMAGETYPE_JPX' => 'class:imagick',
        'IMAGETYPE_JB2' => 'class:imagick',
        'IMAGETYPE_SWC' => 'class:imagick',
        'IMAGETYPE_IFF' => 'class:imagick',
        'IMAGETYPE_WBMP' => 'imagecreatefromwbmp',
        'IMAGETYPE_XBM' => 'imagecreatefromxbm',
        'IMAGETYPE_ICO' => 'class:imagick'
    );

    /**
     * @var string
     */
    protected $source_file;

    /**
     * @var string
     */
    protected $image_type;

    /**
     * @var boolean
     */
    protected $ready = false;

    /**
     * @var string
     */
    protected $extension;

    /**
     * @var string
     */
    protected $real_extension;

    /**
     * @var object|resource
     */
    protected $resource;

    /**
     * @var integer
     */
    protected $width;

    /**
     * @var integer
     */
    protected $height;
    /**
     * @var resource|object resized
     */
    protected $image_resized;

    /**
     * @var bool
     */
    private static $imagick_exist;

    /**
     * @var array
     */
    protected $last_set_image = array(
        'height' => false,
        'width' => false,
        'mode' => false
    );

    /**
     * ImageResize constructor.
     */
    public function __construct()
    {
        parent::__construct();
        if (!isset(self::$imagick_exist)) {
            self::$imagick_exist = class_exists('Imagick');
        }
    }

    /**
     * @return bool
     */
    public function useImagick()
    {
        return self::$imagick_exist;
    }

    /**
     * Initial create
     * @param string $fileName
     *
     * @return $this
     */
    public static function create($fileName)
    {
        // create new current class object
        $res = new self();
        // clear
        $res->clear();
        $res->source_file = $fileName;
        if (is_string($res->source_file) && is_file($res->source_file)) {
            $res->extension = pathinfo($res->source_file, PATHINFO_EXTENSION);
            $res->createResourceOfFile();
            return $res;
        }
        trigger_error(
            'File does not exists',
            E_USER_WARNING
        );

        $res->ready = false;
        $res->image_type = null;
        return $res;
    }

    /**
     * Determine real type and set some property
     *
     * @return void
     */
    private function createResourceOfFile()
    {
        $type = @exif_imagetype($this->source_file);
        if ($type === false || !isset($this->image_type_list[$type])) {
            return;
        }

        $this->image_type = $this->image_type_list[$type][0];
        $this->real_extension = $this->image_type_list[$type][1];
        if ($this->useImagick() || $this->image_type_function[$this->image_type] == 'class:imagick') {
            if (!class_exists('Imagick')) {
                trigger_error(
                    'Php Imagick does not exist in server. Please check configuration',
                    E_USER_WARNING
                );
                return;
            }

            $this->resource = new Imagick($this->source_file);
            $this->width  = $this->resource->getImageWidth();
            $this->height = $this->resource->getImageHeight();
            $this->ready = true;
            return;
        }

        if (!function_exists($this->image_type_function[$this->image_type])) {
            trigger_error(
                sprintf(
                    'Function %s does not exist in server. Please check configuration',
                    $this->image_type_function[$this->image_type]
                ),
                E_USER_WARNING
            );
        }

        $this->resource = $this->image_type_function[$this->image_type]($this->source_file);
        $this->width  = imagesx($this->resource);
        $this->height = imagesy($this->resource);
        $this->ready = true;
    }

    /**
     * Clearing default
     */
    public function clear()
    {
        $this->ready = false;
        $this->height = null;
        $this->width = null;
        $this->source_file = null;
        $this->extension = null;
        $this->real_extension = null;
        // destroy
        if (is_resource($this->image_resized)) {
            imagedestroy($this->image_resized);
        }
        $this->image_resized = null;
        if (is_resource($this->resource)) {
            imagedestroy($this->resource);
        }
        $this->resource = null;
    }

    /**
     * Check if is ready
     *
     * @return bool
     */
    public function isReady()
    {
        return $this->ready;
    }

    /**
     * Set As Cropped
     *
     * @param integer $newWidth
     * @param integer $newHeight
     *
     * @return ImageResizer|null
     */
    public function crop($newWidth, $newHeight)
    {
        return $this->resize($newWidth, $newHeight, 'crop');
    }

    /**
     * Set As auto
     *
     * @param integer $newWidth
     * @param integer $newHeight
     *
     * @return ImageResizer|null
     */
    public function auto($newWidth, $newHeight)
    {
        return $this->resize($newWidth, $newHeight, 'auto');
    }

    /**
     * Set As exactly
     *
     * @param integer $newWidth
     * @param integer $newHeight
     *
     * @return ImageResizer|null
     */
    public function exact($newWidth, $newHeight)
    {
        return $this->resize($newWidth, $newHeight, 'exact');
    }

    /**
     * Rotate
     *
     * @param integer $degree
     *
     * @return $this|bool
     */
    public function rotate($degree)
    {
        if ($this->isReady()) {
            $this->image_resized = imagerotate($this->resource, $degree, 0);

            return $this;
        }
        return false;
    }

    /**
     * Set As Potrait
     *
     * @param integer $newWidth
     * @param integer $newHeight
     *
     * @return ImageResizer|null
     */
    public function potrait($newWidth, $newHeight)
    {
        return $this->resize($newWidth, $newHeight, 'potrait');
    }

    /**
     * @param integer $newWidth
     * @param integer $newHeight
     *
     * @return ImageResizer|null
     */
    public function landscape($newWidth, $newHeight)
    {
        return $this->resize($newWidth, $newHeight, 'landscape');
    }

    /**
     * @param integer $newWidth
     * @param integer $newHeight
     * @param string $option
     *
     * @return $this|null
     */
    public function resize($newWidth, $newHeight, $option = "crop")
    {
        if (!$this->isReady()) {
            return null;
        }

        $option = !is_string($option) ? 'crop' : trim(strtolower($option));
        // *** Get optimal width and height - based on $option
        $optionArray = $this->getDimensions($newWidth, $newHeight, $option);
        $optimalWidth  = $optionArray['w'];
        $optimalHeight = $optionArray['h'];

        $this->last_set_image = array(
            'height' => $newHeight,
            'width' => $newWidth,
            'mode' => $option
        );

        if ($this->useImagick()) {
            $this->image_resized = clone $this->resource;
            $this->image_resized->resizeImage($optimalWidth, $optimalHeight, Imagick::FILTER_LANCZOS, 1);
            $retval = $this->image_resized->cropImage(
                $newWidth, $newHeight,
                (($optimalWidth - $newWidth) / 2),
                (($optimalHeight - $newHeight) / 2)
            );
            $this->ready = $retval;
            return $this;
        }

        $resource = is_resource($this->image_resized) ? $this->image_resized : $this->resource;
        $this->image_resized = imagecreatetruecolor($optimalWidth, $optimalHeight);
        imagecopyresampled(
            $this->image_resized,
            $resource,
            0,
            0,
            0,
            0,
            $optimalWidth,
            $optimalHeight,
            $this->width,
            $this->height
        );

        // *** if option is 'cropProcess', then cropProcess too
        if ($option == 'crop') {
            $this->cropProcess($optimalWidth, $optimalHeight, $newWidth, $newHeight);
        }

        return $this;
    }

    /**
     * Getting Dimension
     *
     * @param integer $newWidth
     * @param integer $newHeight
     * @param string $option
     *
     * @return array
     */
    private function getDimensions($newWidth, $newHeight, $option)
    {
        switch ($option)
        {
            case 'exact':
                $optimalWidth = $newWidth;
                $optimalHeight= $newHeight;
                break;
            case 'portrait':
                $optimalWidth = $this->getSizeByFixedHeight($newHeight);
                $optimalHeight= $newHeight;
                break;
            case 'landscape':
                $optimalWidth = $newWidth;
                $optimalHeight= $this->getSizeByFixedWidth($newWidth);
                break;
            case 'auto':
                $optionArray = $this->getSizeByAuto($newWidth, $newHeight);
                $optimalWidth = $optionArray['w'];
                $optimalHeight = $optionArray['h'];
                break;
            default:
                $optionArray = $this->getOptimalCrop($newWidth, $newHeight);
                $optimalWidth = $optionArray['w'];
                $optimalHeight = $optionArray['h'];
                break;
        }

        return array('w' => $optimalWidth, 'h' => $optimalHeight);
    }

    /**
     * @param integer $newHeight
     *
     * @return mixed
     */
    private function getSizeByFixedHeight($newHeight)
    {
        $ratio = $this->width / $this->height;
        $newWidth = $newHeight * $ratio;
        return $newWidth;
    }

    /**
     * @param integer $newWidth
     *
     * @return mixed
     */
    private function getSizeByFixedWidth($newWidth)
    {
        $ratio = $this->height / $this->width;
        $newHeight = $newWidth * $ratio;
        return $newHeight;
    }

    /**
     * @param integer $newWidth
     * @param integer $newHeight
     *
     * @return array
     */
    private function getSizeByAuto($newWidth, $newHeight)
    {
        if ($this->height < $this->width)
            // *** Image to be resized is wider (landscape)
        {
            $optimalWidth = $newWidth;
            $optimalHeight= $this->getSizeByFixedWidth($newWidth);
        }
        elseif ($this->height > $this->width)
            // *** Image to be resized is taller (portrait)
        {
            $optimalWidth = $this->getSizeByFixedHeight($newHeight);
            $optimalHeight= $newHeight;
        } else
            // *** Image to be resizerd is a square
        {
            if ($newHeight < $newWidth) {
                $optimalWidth = $newWidth;
                $optimalHeight= $this->getSizeByFixedWidth($newWidth);
            } else if ($newHeight > $newWidth) {
                $optimalWidth = $this->getSizeByFixedHeight($newHeight);
                $optimalHeight= $newHeight;
            } else {
                // *** Sqaure being resized to a square
                $optimalWidth = $newWidth;
                $optimalHeight= $newHeight;
            }
        }

        return array('w' => $optimalWidth, 'h' => $optimalHeight);
    }

    /**
     * @param integer $newWidth
     * @param integer $newHeight
     *
     * @return array
     */
    private function getOptimalCrop($newWidth, $newHeight)
    {

        $heightRatio = $this->height / $newHeight;
        $widthRatio  = $this->width /  $newWidth;

        if ($heightRatio < $widthRatio) {
            $optimalRatio = $heightRatio;
        } else {
            $optimalRatio = $widthRatio;
        }

        $optimalHeight = $this->height / $optimalRatio;
        $optimalWidth  = $this->width  / $optimalRatio;

        return array('w' => $optimalWidth, 'h' => $optimalHeight);
    }

    /**
     * @param integer $optimalWidth
     * @param integer $optimalHeight
     * @param integer $newWidth
     * @param integer $newHeight
     *
     * @return bool
     */
    private function cropProcess($optimalWidth, $optimalHeight, $newWidth, $newHeight)
    {
        if (!$this->isReady()) {
            return false;
        }

        // *** Find center - this will be used for the cropProcess
        $cropStartX = ( $optimalWidth / 2) - ( $newWidth /2 );
        $cropStartY = ( $optimalHeight/ 2) - ( $newHeight/2 );

        $crop = $this->image_resized;
        // *** Now cropProcess from center to exact requested size
        $this->image_resized = imagecreatetruecolor($newWidth , $newHeight);
        imagecopyresampled(
            $this->image_resized,
            $crop ,
            0,
            0,
            $cropStartX,
            $cropStartY,
            $newWidth,
            $newHeight ,
            $newWidth,
            $newHeight
        );
        return true;
    }

    /**
     * @param string $savePath
     * @param integer $imageQuality
     * @param bool   $force
     *
     * @return bool
     */
    public function saveTo($savePath, $imageQuality = 100, $force = false)
    {
        if (!$this->isReady()) {
            return false;
        }

        // check if has on cropProcess
        if (!isset($this->image_resized)) {
            if ($this->last_set_image['width'] === false) {
                $this->image_resized = $this->resource;
            } else {
                // set from last result
                $this->resize(
                    $this->last_set_image['width'],
                    $this->last_set_image['height'],
                    $this->last_set_image['mode']
                );
            }
        }

        // *** Get extension
        $extension = pathinfo($savePath, PATHINFO_EXTENSION);
        // file exist
        if (file_exists($savePath)) {
            if (!$force) {
                return false;
            }
            if (!is_writable($savePath)) {
                trigger_error(
                    'File exist! And could not to be replace',
                    E_USER_WARNING
                );
                return false;
            }
        }

        $dirname = dirname($savePath);
        if (!$dirname || ! ($dirname = realpath($dirname))) {
            trigger_error(
                'Directory Target Does not exist. Resource image resize cleared.',
                E_USER_WARNING
            );
            return false;
        }
        if (!is_writable($dirname)) {
            trigger_error(
                'Directory Target is not writable. Please check directory permission.',
                E_USER_WARNING
            );
            return false;
        }
        if ($this->useImagick()) {
            $this->image_resized->setImageFormat($extension);
            if (!$fp = @fopen($savePath, 'wb')) {
                trigger_error(
                    'Could not write into your target directory. Resource image resize cleared.',
                    E_USER_WARNING
                );
                $this->image_resized->clear();
                $this->image_resized = null;
                return false;
            }
            $this->image_resized->imageWriteFile($fp);
            @fclose($fp);
            $this->image_resized->clear();
            $this->image_resized = null;
            return true;
        }

        switch($extension) {
            case 'jpg':
            case 'jpeg':
                     imagejpeg($this->image_resized, $savePath, $imageQuality);
                break;
            case 'wbmp':
            case 'bmp':
                     imagewbmp($this->image_resized, $savePath, $imageQuality);
                break;
            case 'gif':
                     imagegif($this->image_resized, $savePath);
                break;
            case 'xbm':
                    imagexbm($this->image_resized, $savePath);
                break;
            case 'png':
                    $scaleQuality = round(($imageQuality/100) * 9);
                    $invertScaleQuality = 9 - $scaleQuality;
                    imagepng($this->image_resized, $savePath, $invertScaleQuality);
                break;
            default:
                    return false;
                break;
        }

        imagedestroy($this->image_resized);
        $this->image_resized = null;

        return true;
    }

    /**
     * Magic method destruct
     */
    public function __destruct()
    {
        parent::__destruct();
        $this->clear();
    }
}
