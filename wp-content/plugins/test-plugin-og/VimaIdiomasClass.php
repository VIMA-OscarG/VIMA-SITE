<?php
class VimaIdiomasClass {
    private $translations = [];
    private $filePath;
    private $translateEnabled = false;

    
    private $currentLanguage = 'en'; 
    private $targetLanguage = 'es'; 

    public function __construct($filePath) {
        $this->filePath = realpath($filePath);

        if ($this->translateEnabled) {
            $this->loadTranslations();
        }
    }

    private function loadTranslations() {
        $jsonContent = @file_get_contents($this->filePath);

        if ($jsonContent === false) {
            //echo "<pre>".print_r(plugin_dir_url(__FILE__), true). "</pre>";
            echo 'Error: No se pudo leer el archivo de traduccion en la ruta: ' . htmlspecialchars($this->filePath) . "<br>";
            return;
        }

        $this->translations = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo 'Error: El archivo de traduccion no es un JSON valido.<br>';
            return;
        }
    }

    // function para traducir el texto
    public function translate($text) {
        if ($this->translateEnabled) {
            return isset($this->translations[$text]) ? $this->translations[$text] : $text;
        }
        return $text; 
    }

    public function print($text) {
        echo $this->translate($text);
    }

    // Getter para currentLanguage
    public function getCurrentLanguage() {
        return $this->currentLanguage;
    }

    // Setter para currentLanguage
    public function setCurrentLanguage($language) {
        $this->currentLanguage = $language;
        $this->updateTranslateFlag();
    }

    // Getter para targetLanguage
    public function getTargetLanguage() {
        return $this->targetLanguage;
    }

    // Setter para targetLanguage
    public function setTargetLanguage($language) {
        $this->targetLanguage = $language;
        $this->updateTranslateFlag();
    }

    // function privada para actualizar el flag de traduccion
    private function updateTranslateFlag() {
        $this->translateEnabled = ($this->currentLanguage !== $this->targetLanguage);
        // Recargar traducciones si el flag se habilita
        if ($this->translateEnabled) {
            $this->loadTranslations();
        }
    }
}