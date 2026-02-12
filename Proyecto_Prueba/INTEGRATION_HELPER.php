<?php
/**
 * Clase de Ayuda para Integración Nexus Chat
 * Copia este archivo en tu "Proyecto_Prueba" (ej: includes/ChatIntegration.php)
 */
class ChatIntegration
{
    private $appKey;
    private $chatUrl;

    public function __construct($appKey, $chatUrl)
    {
        $this->appKey = $appKey;
        $this->chatUrl = rtrim($chatUrl, '/');
    }

    /**
     * Genera la URL del frame de chat o los parámetros para AJAX
     */
    public function getAuthParams($userId)
    {
        // La firma DEBE coincidir con la lógica en ChatController::verifySignature
        // Payload = user_id (simple)
        $signature = hash_hmac('sha256', (string) $userId, $this->appKey);

        return [
            'user_id' => $userId,
            'signature' => $signature,
            'endpoint' => $this->chatUrl
        ];
    }

    /**
     * Imprime el script JS para conectar automáticamente
     */
    public function printInitScript($userId)
    {
        $auth = $this->getAuthParams($userId);

        echo "<script>
            window.NexusChatConfig = " . json_encode($auth) . ";
            console.log('Nexus Chat Configurado para Usuario:', " . $userId . ");
        </script>";
    }
}
?>