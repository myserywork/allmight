<?php
/**
 * Env Helper - Carrega variáveis de ambiente do arquivo .env
 * 
 * Carregar no autoload.php: $autoload['helper'] = array('env');
 */

if (!function_exists('load_env')) {
    /**
     * Carrega variáveis do arquivo .env
     * @param string $path Caminho para o arquivo .env
     */
    function load_env($path = null) {
        if ($path === null) {
            $path = FCPATH . '.env';
        }
        
        if (!file_exists($path)) {
            return false;
        }
        
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Ignorar comentários
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Ignorar linhas sem =
            if (strpos($line, '=') === false) {
                continue;
            }
            
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Remover aspas se existirem
            $value = trim($value, '"\'');
            
            // Definir variável de ambiente se não existir
            if (!getenv($name)) {
                putenv("$name=$value");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
        
        return true;
    }
}

if (!function_exists('env')) {
    /**
     * Obtém uma variável de ambiente
     * @param string $key Nome da variável
     * @param mixed $default Valor padrão se não existir
     * @return mixed
     */
    function env($key, $default = null) {
        $value = getenv($key);
        
        if ($value === false) {
            $value = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
        }
        
        // Converter valores especiais
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'null':
            case '(null)':
                return null;
            case 'empty':
            case '(empty)':
                return '';
        }
        
        return $value;
    }
}
