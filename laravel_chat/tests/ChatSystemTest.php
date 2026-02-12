<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;

class ChatSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar sesión PHP para pruebas
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Test de obtención de usuario actual
     */
    public function test_get_current_user_authenticated()
    {
        // Crear usuario de prueba
        $user = User::factory()->create([
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'correo' => 'juan@test.com'
        ]);

        // Establecer sesión PHP
        $_SESSION['usuario_id'] = $user->id;
        $_SESSION['nombre'] = $user->nombre;
        $_SESSION['apellido'] = $user->apellido;

        // Realizar petición
        $response = $this->post('/chat/devuelve');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'id_usuario' => $user->id,
                    'usuario' => 'Juan Pérez'
                ]);
    }

    /**
     * Test de obtención de usuario no autenticado
     */
    public function test_get_current_user_unauthenticated()
    {
        // Limpiar sesión
        session_destroy();
        session_start();

        $response = $this->post('/chat/devuelve');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => false
                ]);
    }

    /**
     * Test de envío de mensaje
     */
    public function test_send_message()
    {
        // Crear usuarios
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Establecer sesión
        $_SESSION['usuario_id'] = $user1->id;
        $_SESSION['nombre'] = $user1->nombre;
        $_SESSION['apellido'] = $user1->apellido;

        // Datos del mensaje
        $messageData = [
            'sala' => $user1->id . '-' . $user2->id,
            'mensaje' => 'Mensaje de prueba'
        ];

        $response = $this->post('/chat/mensaje', $messageData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Mensaje enviado'
                ]);

        // Verificar que el mensaje se guardó en la base de datos
        $this->assertDatabaseHas('mensajes', [
            'sala' => $messageData['sala'],
            'usuario' => $user1->id,
            'mensaje' => $messageData['mensaje']
        ]);
    }

    /**
     * Test de obtención de contactos
     */
    public function test_get_contacts()
    {
        // Crear usuarios
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Crear mensajes entre usuarios
        Message::factory()->create([
            'sala' => $user1->id . '-' . $user2->id,
            'usuario' => $user1->id,
            'mensaje' => 'Hola'
        ]);

        // Establecer sesión
        $_SESSION['usuario_id'] = $user1->id;
        $_SESSION['nombre'] = $user1->nombre;
        $_SESSION['apellido'] = $user1->apellido;

        $response = $this->get('/chat/contactos');

        $response->assertStatus(200)
                ->assertJsonCount(1);
    }

    /**
     * Test de obtención de historial de mensajes
     */
    public function test_get_messages()
    {
        // Crear usuarios
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Crear mensajes
        $sala = $user1->id . '-' . $user2->id;
        Message::factory()->count(3)->create([
            'sala' => $sala,
            'usuario' => $user1->id
        ]);

        // Establecer sesión
        $_SESSION['usuario_id'] = $user1->id;
        $_SESSION['nombre'] = $user1->nombre;
        $_SESSION['apellido'] = $user1->apellido;

        $response = $this->get("/chat/mensajes/{$sala}");

        $response->assertStatus(200)
                ->assertJsonCount(3);
    }

    /**
     * Test de indicador de escritura
     */
    public function test_send_typing_indicator()
    {
        // Crear usuarios
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Establecer sesión
        $_SESSION['usuario_id'] = $user1->id;
        $_SESSION['nombre'] = $user1->nombre;
        $_SESSION['apellido'] = $user1->apellido;

        $typingData = [
            'sala' => $user1->id . '-' . $user2->id,
            'is_typing' => true
        ];

        $response = $this->post('/chat/typing', $typingData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ]);
    }

    /**
     * Test de obtención de usuarios en línea
     */
    public function test_get_online_users()
    {
        // Crear usuarios
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Marcar usuario como en línea
        Cache::put('user-online-' . $user1->id, true, now()->addMinutes(5));

        $response = $this->get('/chat/online');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    $user1->id => [
                        'id',
                        'nombre_completo',
                        'foto_perfil'
                    ]
                ]);
    }

    /**
     * Test de logout
     */
    public function test_logout()
    {
        // Crear usuario
        $user = User::factory()->create();

        // Establecer sesión
        $_SESSION['usuario_id'] = $user->id;
        $_SESSION['nombre'] = $user->nombre;
        $_SESSION['apellido'] = $user->apellido;

        $response = $this->post('/chat/logout');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ]);

        // Verificar que el usuario ya no está en línea
        $this->assertFalse(Cache::has('user-online-' . $user->id));
    }

    /**
     * Test de acceso denegado a sala de chat
     */
    public function test_unauthorized_chat_room_access()
    {
        // Crear usuarios
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        // Establecer sesión como user1
        $_SESSION['usuario_id'] = $user1->id;
        $_SESSION['nombre'] = $user1->nombre;
        $_SESSION['apellido'] = $user1->apellido;

        // Intentar acceder a sala de user2-user3 (sin user1)
        $sala = $user2->id . '-' . $user3->id;

        $response = $this->get("/chat/mensajes/{$sala}");

        $response->assertStatus(403)
                ->assertJson([
                    'error' => 'No tienes acceso a esta sala'
                ]);
    }

    /**
     * Test de generación de sala
     */
    public function test_room_generation()
    {
        $user1Id = 5;
        $user2Id = 10;

        // Test con user1 < user2
        $sala1 = Message::generarSala($user1Id, $user2Id);
        $this->assertEquals('5-10', $sala1);

        // Test con user2 < user1
        $sala2 = Message::generarSala($user2Id, $user1Id);
        $this->assertEquals('5-10', $sala2);

        // Verificar que ambas generen la misma sala
        $this->assertEquals($sala1, $sala2);
    }

    /**
     * Test de modelo User
     */
    public function test_user_model_methods()
    {
        $user = User::factory()->create([
            'nombre' => 'María',
            'apellido' => 'García',
            'foto_perfil' => 'maria.jpg'
        ]);

        // Test nombre completo
        $this->assertEquals('María García', $user->nombre_completo);

        // Test URL foto de perfil
        $this->assertStringContains('maria.jpg', $user->foto_perfil_url);

        // Test estado en línea
        $this->assertFalse($user->is_online);

        // Marcar como en línea
        $user->markAsOnline();
        $this->assertTrue($user->is_online);

        // Marcar como desconectado
        $user->markAsOffline();
        $this->assertFalse($user->is_online);
    }

    /**
     * Test de modelo Message
     */
    public function test_message_model_methods()
    {
        $user = User::factory()->create();
        $message = Message::factory()->create([
            'usuario' => $user->id,
            'mensaje' => 'Mensaje de prueba',
            'fecha' => now()
        ]);

        // Test nombre remitente
        $this->assertEquals($user->nombre_completo, $message->nombre_remitente);

        // Test tiempo formateado
        $this->assertIsString($message->tiempo_formateado);

        // Test marcar como leído
        $this->assertFalse($message->leido);
        $message->marcarComoLeido();
        $this->assertTrue($message->leido);
    }

    /**
     * Test de validación de datos
     */
    public function test_message_validation()
    {
        // Crear usuario
        $user = User::factory()->create();

        // Establecer sesión
        $_SESSION['usuario_id'] = $user->id;
        $_SESSION['nombre'] = $user->nombre;
        $_SESSION['apellido'] = $user->apellido;

        // Test mensaje vacío
        $response = $this->post('/chat/mensaje', [
            'sala' => '1-2',
            'mensaje' => ''
        ]);

        $response->assertStatus(422);

        // Test mensaje demasiado largo
        $response = $this->post('/chat/mensaje', [
            'sala' => '1-2',
            'mensaje' => str_repeat('a', 1001)
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test de rendimiento con múltiples mensajes
     */
    public function test_performance_with_multiple_messages()
    {
        // Crear usuarios
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Crear 100 mensajes
        $sala = $user1->id . '-' . $user2->id;
        Message::factory()->count(100)->create([
            'sala' => $sala,
            'usuario' => $user1->id
        ]);

        // Establecer sesión
        $_SESSION['usuario_id'] = $user1->id;
        $_SESSION['nombre'] = $user1->nombre;
        $_SESSION['apellido'] = $user1->apellido;

        $startTime = microtime(true);
        
        $response = $this->get("/chat/mensajes/{$sala}");
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200)
                ->assertJsonCount(100);

        // Verificar que la consulta tome menos de 1 segundo
        $this->assertLessThan(1.0, $executionTime, 'La consulta de mensajes tardó demasiado tiempo');
    }
}
