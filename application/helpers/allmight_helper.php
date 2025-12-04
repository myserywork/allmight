<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * AllMight Helper Functions
 * Funções auxiliares para o sistema
 */

if (!function_exists('format_currency')) {
    /**
     * Formata valor em moeda brasileira
     */
    function format_currency($value) {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }
}

if (!function_exists('format_date')) {
    /**
     * Formata data no padrão brasileiro
     */
    function format_date($date, $format = 'd/m/Y') {
        if (empty($date)) return '-';
        return date($format, strtotime($date));
    }
}

if (!function_exists('format_datetime')) {
    /**
     * Formata data e hora no padrão brasileiro
     */
    function format_datetime($datetime) {
        if (empty($datetime)) return '-';
        return date('d/m/Y H:i', strtotime($datetime));
    }
}

if (!function_exists('format_cnpj')) {
    /**
     * Formata CNPJ no padrão brasileiro
     */
    function format_cnpj($cnpj) {
        if (empty($cnpj)) return '-';
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        if (strlen($cnpj) != 14) return $cnpj;
        return sprintf('%s.%s.%s/%s-%s', 
            substr($cnpj, 0, 2), 
            substr($cnpj, 2, 3), 
            substr($cnpj, 5, 3), 
            substr($cnpj, 8, 4), 
            substr($cnpj, 12, 2)
        );
    }
}

if (!function_exists('situacao_badge')) {
    /**
     * Retorna HTML do badge de situação
     */
    function situacao_badge($situacao) {
        $colors = [
            'Aberta' => 'green',
            'Em andamento' => 'blue',
            'Suspensa' => 'yellow',
            'Encerrada' => 'gray',
            'Cancelada' => 'red',
            'Deserta' => 'orange'
        ];
        
        $color = $colors[$situacao] ?? 'gray';
        
        return sprintf(
            '<span class="inline-flex items-center rounded-full bg-%s-500/20 px-2 py-1 text-xs font-medium text-%s-400">%s</span>',
            $color, $color, $situacao
        );
    }
}

if (!function_exists('status_icon')) {
    /**
     * Retorna ícone baseado no status
     */
    function status_icon($status) {
        $icons = [
            'ativo' => '<i class="fas fa-check-circle text-green-400"></i>',
            'inativo' => '<i class="fas fa-times-circle text-red-400"></i>',
            'pendente' => '<i class="fas fa-clock text-yellow-400"></i>',
            'processando' => '<i class="fas fa-spinner fa-spin text-blue-400"></i>',
            'concluido' => '<i class="fas fa-check text-green-400"></i>',
            'erro' => '<i class="fas fa-exclamation-triangle text-red-400"></i>'
        ];
        
        return $icons[$status] ?? '<i class="fas fa-question-circle text-gray-400"></i>';
    }
}

if (!function_exists('score_stars')) {
    /**
     * Retorna HTML de estrelas baseado no score
     */
    function score_stars($score, $max = 5) {
        $stars = round($score * $max);
        $html = '';
        
        for ($i = 0; $i < $max; $i++) {
            if ($i < $stars) {
                $html .= '<i class="fas fa-star text-yellow-400"></i>';
            } else {
                $html .= '<i class="far fa-star text-gray-600"></i>';
            }
        }
        
        return $html;
    }
}

if (!function_exists('percentage_bar')) {
    /**
     * Retorna HTML de barra de progresso
     */
    function percentage_bar($percentage, $color = 'primary') {
        $percentage = max(0, min(100, $percentage));
        
        return sprintf(
            '<div class="w-full bg-dark-700 rounded-full h-2">
                <div class="bg-%s-500 h-2 rounded-full" style="width: %s%%"></div>
            </div>',
            $color, $percentage
        );
    }
}

if (!function_exists('truncate_text')) {
    /**
     * Trunca texto com ellipsis
     */
    function truncate_text($text, $length = 100) {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length) . '...';
    }
}

if (!function_exists('cnpj_mask')) {
    /**
     * Aplica máscara de CNPJ
     */
    function cnpj_mask($cnpj) {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        if (strlen($cnpj) != 14) {
            return $cnpj;
        }
        
        return sprintf(
            '%s.%s.%s/%s-%s',
            substr($cnpj, 0, 2),
            substr($cnpj, 2, 3),
            substr($cnpj, 5, 3),
            substr($cnpj, 8, 4),
            substr($cnpj, 12, 2)
        );
    }
}

if (!function_exists('time_ago')) {
    /**
     * Retorna quanto tempo atrás
     */
    function time_ago($datetime) {
        if (empty($datetime)) return '-';
        
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;
        
        if ($diff < 60) {
            return 'agora mesmo';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . ' minuto' . ($mins > 1 ? 's' : '') . ' atrás';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hora' . ($hours > 1 ? 's' : '') . ' atrás';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' dia' . ($days > 1 ? 's' : '') . ' atrás';
        } else {
            return format_date($datetime);
        }
    }
}

if (!function_exists('uf_name')) {
    /**
     * Retorna nome completo do estado
     */
    function uf_name($uf) {
        $states = [
            'AC' => 'Acre', 'AL' => 'Alagoas', 'AP' => 'Amapá', 'AM' => 'Amazonas',
            'BA' => 'Bahia', 'CE' => 'Ceará', 'DF' => 'Distrito Federal', 'ES' => 'Espírito Santo',
            'GO' => 'Goiás', 'MA' => 'Maranhão', 'MT' => 'Mato Grosso', 'MS' => 'Mato Grosso do Sul',
            'MG' => 'Minas Gerais', 'PA' => 'Pará', 'PB' => 'Paraíba', 'PR' => 'Paraná',
            'PE' => 'Pernambuco', 'PI' => 'Piauí', 'RJ' => 'Rio de Janeiro', 'RN' => 'Rio Grande do Norte',
            'RS' => 'Rio Grande do Sul', 'RO' => 'Rondônia', 'RR' => 'Roraima', 'SC' => 'Santa Catarina',
            'SP' => 'São Paulo', 'SE' => 'Sergipe', 'TO' => 'Tocantins'
        ];
        
        return $states[$uf] ?? $uf;
    }
}

if (!function_exists('file_size_format')) {
    /**
     * Formata tamanho de arquivo
     */
    function file_size_format($bytes) {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }
        
        return $bytes;
    }
}

if (!function_exists('alert_html')) {
    /**
     * Retorna HTML de alert
     */
    function alert_html($message, $type = 'info') {
        $icons = [
            'success' => 'fa-check-circle',
            'error' => 'fa-exclamation-circle',
            'warning' => 'fa-exclamation-triangle',
            'info' => 'fa-info-circle'
        ];
        
        $colors = [
            'success' => 'green',
            'error' => 'red',
            'warning' => 'yellow',
            'info' => 'blue'
        ];
        
        $icon = $icons[$type] ?? 'fa-info-circle';
        $color = $colors[$type] ?? 'blue';
        
        return sprintf(
            '<div class="glass rounded-lg border-l-4 border-%s-500 p-4 mb-4">
                <div class="flex items-center">
                    <i class="fas %s text-%s-400 text-xl mr-3"></i>
                    <p class="text-white">%s</p>
                </div>
            </div>',
            $color, $icon, $color, $message
        );
    }
}
