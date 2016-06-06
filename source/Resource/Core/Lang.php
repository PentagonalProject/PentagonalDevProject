<?php
/**
 * Beta
 */
use Gettext\Translator;
use Gettext\Translations;

class CI_Lang
{
    /**
     * List of translations
     *
     * @var	array
     */
    public $language = array();

    public $current_language = 'en';

    /**
     * List of loaded language files
     *
     * @var	array
     */
    public $is_loaded = array();

    /**
     * Predefined System Text Domain
     * this is reserved text domain and not allowed to use
     *
     * @cont string
     */
    const SYSTEM_TEXTDOMAIN = '__system__reserved__';

    /**
     * @var string
     */
    const DEFAULT_TEXTDOMAIN = '__default__reserved__';

    /**
     * @var array
     */
    protected $reserved_text_domain = array(
        'calendar',
        'date',
        'db',
        'email',
        'form_validation',
        'ftp',
        'imglib',
        'migration',
        'number',
        'pagination',
        'profiler',
        'unit_test',
        'upload',
    );

    /**
     * @var array
     */
    protected $textDomain = array();

    /**
     * Class constructor
     *
     * @return	void
     */
    public function __construct()
    {
        log_message('info', 'Language Class Initialized');
        $this->current_language = config_item('language');
        if (!$this->current_language || !is_string($this->current_language) || trim($this->current_language) == '') {
            get_config(array('language' => 'en'));
            $this->current_language  = 'en';
        }
        $this->textDomain[self::SYSTEM_TEXTDOMAIN] = LANGUAGEPATH . 'System' . DIRECTORY_SEPARATOR;
        $this->load(self::SYSTEM_TEXTDOMAIN);
    }

    /**
     * @return string
     */
    public function getCurrentLanguage() {
        return $this->current_language;
    }

    /**
     * Translate
     *
     * @param string $language
     * @param null   $text_domain
     *
     * @return mixed
     */
    public function translate($language, $text_domain = null)
    {
        if (!$text_domain) {
            return $this->translateSystem($language);
        }

        return $language;
    }
    /**
     * Load language text domain just for backwards compatibility
     *
     * @param string $langfile
     * @param string $altPath
     * @param string $textdomain
     *
     * @return bool|void
     */
    public function load($textdomain = null, $force = false)
    {
        // override the language files to do
        if (! $textdomain || is_string($textdomain) && file_exists(BASEPATH . 'language/english/'.$textdomain . '_helper.php')) {
            $textdomain = self::SYSTEM_TEXTDOMAIN;
        }
        if ($textdomain && !is_string($textdomain)) {
            return false;
        }
        $language = $this->getCurrentLanguage();
        if ($textdomain == self::SYSTEM_TEXTDOMAIN && ! empty($this->is_loaded[self::SYSTEM_TEXTDOMAIN])) {
            return true;
        }
        if ($textdomain != self::SYSTEM_TEXTDOMAIN) {
            if (! empty($this->is_loaded[$textdomain])
                && (
                    $this->is_loaded[$textdomain] !== true && $force
                    || false
                )
            ) {
                return 1;
            }
        }

        if (($idiom = strpos($language, '_')) !== false) {
            $ex = explode('_', $language);
            do {
                $idiom = reset($ex);
                if (trim($idiom) == '') {
                    $exist = false;
                    array_shift($ex);
                } else {
                    $exist = true;
                }
            } while(! $exist && ! empty($ex));
        }

        /**
         * Load system first
         */
        if (empty($this->language[self::SYSTEM_TEXTDOMAIN])
            && empty($this->is_loaded[self::SYSTEM_TEXTDOMAIN])
        ) {
            $file_path = $this->getPathFor(self::SYSTEM_TEXTDOMAIN);
            if (!file_exists($file = $file_path . $language . '.mo')) {
                if (!file_exists($file = $file_path . $language . '.po')) {
                    if (!file_exists($file = $file_path . $idiom . '.mo')) {
                        $file = $file_path . $idiom . '.po';
                    }
                }
            }

            if (file_exists($file)) {
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                $this->language[self::SYSTEM_TEXTDOMAIN] = new Translator();
                if ($ext == 'mo') {
                    $translation = Translations::fromMoFile($file);
                    $translation->setDomain(self::SYSTEM_TEXTDOMAIN);
                    $this->language[self::SYSTEM_TEXTDOMAIN]->loadTranslations($translation);
                } else {
                    /**
                     * @var \Gettext\Translations
                     */
                    $translation = Translations::fromPoFile($file);
                    $translation->setDomain(self::SYSTEM_TEXTDOMAIN);
                    $this->language[self::SYSTEM_TEXTDOMAIN]->loadTranslations($translation);
                }
                $this->is_loaded[self::SYSTEM_TEXTDOMAIN] = basename($file);
            } else {
                $this->is_loaded[self::SYSTEM_TEXTDOMAIN] = true;
            }
        }

        /**
         * Check if text domain is system
         */
        if ($textdomain == self::SYSTEM_TEXTDOMAIN) {
            return true;
        }

        $file_path = $this->getPathFor(self::SYSTEM_TEXTDOMAIN);
        if (!$file_path) {
            return false;
        }
        if (!file_exists($file = $file_path . $language . '.mo')) {
            if (!file_exists($file = $file_path . $language . '.po')) {
                if (!file_exists($file = $file_path . $idiom . '.mo')) {
                    if (!file_exists($file = $file_path . $idiom . '.po')) {
                        $this->is_loaded[$textdomain] = true;
                        return false;
                    }
                }
            }
        }

        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $this->language[$textdomain] = new Translator();
        if ($ext == 'mo') {
            $translation = Translations::fromMoFile($file);
            $translation->setDomain($textdomain);
            $this->language[$textdomain]->loadTranslations($translation);
        } else {
            /**
             * @var \Gettext\Translations
             */
            $translation = Translations::fromPoFile($file);
            $translation->setDomain($textdomain);
            $this->language[$textdomain]->loadTranslations($translation);
        }

        $this->is_loaded[$textdomain] = basename($file);

        return true;
    }

    /**
     * Translate system language
     *
     * @param string $language
     *
     * @return mixed
     */
    public function translateSystem($language)
    {
        if (!is_string($language)) {
            return $language;
        }
        if (!empty($this->language[self::SYSTEM_TEXTDOMAIN])) {
            $language = $this->language[self::SYSTEM_TEXTDOMAIN]->gettext($language);
        }

        return $language;
    }

    /**
     * Load the text domain
     *
     * @param string $textDomain
     * @return boolean
     */
    public function loadTextDomain($textDomain, $path)
    {
        if ($textDomain == self::SYSTEM_TEXTDOMAIN) {
            trigger_error(
                sprintf(
                    'Invalid text domain set. %s is system text domain ,not allowed to used.',
                    $textDomain
                ),
                E_USER_WARNING
            );
            return false;
        }
        if (!is_string($textDomain)) {
            trigger_error(
                'Invalid text domain set. Text domain must be as string.',
                E_USER_WARNING
            );
            return false;
        }
        $textDomain = trim($textDomain);
        if (in_array($textDomain, $this->reserved_text_domain)) {
            trigger_error(
                sprintf(
                    'Invalid text domain set. %s is system text domain ,not allowed to used.',
                    $textDomain
                ),
                E_USER_WARNING
            );
            return false;
        }

        if (is_string($path)) {
            $path = rtrim(preg_replace('/(\\\|\/)+/', DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
            $path .= DIRECTORY_SEPARATOR;
            $this->textDomain[$textDomain] = $path;
            return $this->load($textDomain);
        } else {
            trigger_error(
                sprintf(
                    'Invalid path for text domain %s.Path must be as string',
                    $textDomain
                ),
                E_USER_NOTICE
            );
            return false;
        }
    }

    /**
     * Get Path text domain
     * @param string $textdomain
     *
     * @return string|null
     */
    public function getPathFor($textdomain)
    {
        if (isset($this->textDomain[$textdomain]) && is_dir($this->textDomain[$textdomain])) {
            return $this->textDomain[$textdomain];
        }

        return null;
    }

    /**
     * Language line
     *
     * Fetches a single line of text from the language array
     *
     * @param	string	$line		Language line key
     * @param	bool	$log_errors	Whether to log an error message if the line is not found
     * @return	string	Translation
     */
    public function line($line, $textdomain = null, $log_errors = true)
    {
        if (!is_string($line)) {
            $log_errors === true && log_message('error', 'Could not find the language line "'.$line.'"');
            return $line;
        }

        return $this->translate($line, $textdomain);
    }
}
