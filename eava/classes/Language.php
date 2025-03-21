<?php
class Language {
    private static $instance = null;
    private $translations = [];
    private $currentLang = 'en';
    private $availableLangs = ['en', 'sw', 'fr'];
    private $defaultLang = 'en';

    private function __construct() {
        // Load language preference from session or cookie
        $this->currentLang = $this->loadLanguagePreference();
        $this->loadTranslations();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadLanguagePreference() {
        $session = Session::getInstance();
        
        // Check session first
        if ($session->has('language')) {
            return $session->get('language');
        }

        // Check cookie next
        if (isset($_COOKIE['language'])) {
            return $_COOKIE['language'];
        }

        // Check browser language
        $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', 0, 2);
        if (in_array($browserLang, $this->availableLangs)) {
            return $browserLang;
        }

        // Fall back to default
        return $this->defaultLang;
    }

    public function setLanguage($lang) {
        if (!in_array($lang, $this->availableLangs)) {
            throw new Exception("Language '$lang' is not supported");
        }

        $this->currentLang = $lang;
        
        // Save to session and cookie
        $session = Session::getInstance();
        $session->set('language', $lang);
        setcookie('language', $lang, time() + (86400 * 30), '/'); // 30 days

        // Reload translations
        $this->loadTranslations();
    }

    private function loadTranslations() {
        $langFile = __DIR__ . '/../languages/' . $this->currentLang . '.php';
        
        if (!file_exists($langFile)) {
            // Fall back to default language
            $langFile = __DIR__ . '/../languages/' . $this->defaultLang . '.php';
        }

        if (!file_exists($langFile)) {
            throw new Exception("Language file not found: $langFile");
        }

        $this->translations = require $langFile;
    }

    public function get($key, $replacements = []) {
        $text = $this->translations[$key] ?? $key;

        // Handle replacements
        foreach ($replacements as $search => $replace) {
            $text = str_replace(':' . $search, $replace, $text);
        }

        return $text;
    }

    public function getCurrentLanguage() {
        return $this->currentLang;
    }

    public function getAvailableLanguages() {
        return $this->availableLangs;
    }

    public function getLanguageName($code) {
        $languages = [
            'en' => 'English',
            'sw' => 'Kiswahili',
            'fr' => 'Français'
        ];

        return $languages[$code] ?? $code;
    }

    // Helper function to generate language switcher HTML
    public function renderLanguageSwitcher() {
        $html = '<div class="language-switcher">';
        foreach ($this->availableLangs as $lang) {
            $activeClass = ($lang === $this->currentLang) ? ' active' : '';
            $html .= sprintf(
                '<a href="?lang=%s" class="lang-option%s" data-lang="%s">%s</a>',
                $lang,
                $activeClass,
                $lang,
                $this->getLanguageName($lang)
            );
        }
        $html .= '</div>';

        return $html;
    }

    // Helper function for translation
    public static function t($key, $replacements = []) {
        return self::getInstance()->get($key, $replacements);
    }

    // Helper function to format dates in current language
    public function formatDate($date, $format = 'full') {
        $timestamp = is_string($date) ? strtotime($date) : $date;
        
        switch ($this->currentLang) {
            case 'sw':
                setlocale(LC_TIME, 'sw_KE.UTF-8');
                break;
            case 'fr':
                setlocale(LC_TIME, 'fr_FR.UTF-8');
                break;
            default:
                setlocale(LC_TIME, 'en_US.UTF-8');
        }

        switch ($format) {
            case 'short':
                return strftime('%d/%m/%Y', $timestamp);
            case 'medium':
                return strftime('%d %b %Y', $timestamp);
            case 'full':
                return strftime('%d %B %Y', $timestamp);
            case 'time':
                return strftime('%H:%M', $timestamp);
            case 'datetime':
                return strftime('%d %B %Y %H:%M', $timestamp);
            default:
                return strftime($format, $timestamp);
        }
    }

    // Helper function to format numbers in current language
    public function formatNumber($number, $decimals = 0) {
        switch ($this->currentLang) {
            case 'sw':
                return number_format($number, $decimals, '.', ',');
            case 'fr':
                return number_format($number, $decimals, ',', ' ');
            default:
                return number_format($number, $decimals, '.', ',');
        }
    }

    // Helper function to format currency in current language
    public function formatCurrency($amount, $currency = 'USD') {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'KES' => 'KSh'
        ];

        $symbol = $symbols[$currency] ?? $currency;
        $formattedAmount = $this->formatNumber($amount, 2);

        switch ($this->currentLang) {
            case 'fr':
                return $formattedAmount . ' ' . $symbol;
            default:
                return $symbol . $formattedAmount;
        }
    }
}