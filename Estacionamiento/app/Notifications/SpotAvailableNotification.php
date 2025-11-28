<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SpotAvailableNotification extends Notification
{
    use Queueable;

    public $espacio;

    /**
     * Create a new notification instance.
     */
    public function __construct($espacio)
    {
        $this->espacio = $espacio;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Espacio de estacionamiento disponible')
            ->line('¡Buenas noticias! El espacio ' . $this->espacio->codigo . ' está ahora disponible.')
            ->action('Reservar ahora', url('/reservations'))
            ->line('Gracias por usar nuestro sistema de estacionamiento.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'espacio_codigo' => $this->espacio->codigo,
            'message' => 'El espacio ' . $this->espacio->codigo . ' está ahora disponible.',
        ];
    }
}
