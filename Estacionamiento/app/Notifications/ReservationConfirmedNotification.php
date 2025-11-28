<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationConfirmedNotification extends Notification
{
    use Queueable;

    public $reservation;

    /**
     * Create a new notification instance.
     */
    public function __construct($reservation)
    {
        $this->reservation = $reservation;
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
            ->subject('Reserva de estacionamiento confirmada')
            ->line('Su reserva para el espacio ' . $this->reservation->espacio->codigo . ' ha sido confirmada.')
            ->line('Fecha y hora: ' . $this->reservation->fecha_hora_reserva->format('d/m/Y H:i'))
            ->line('Duración: ' . $this->reservation->duracion . ' minutos')
            ->action('Ver reservas', url('/reservations'))
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
            'espacio_codigo' => $this->reservation->espacio->codigo,
            'fecha_hora' => $this->reservation->fecha_hora_reserva,
            'duracion' => $this->reservation->duracion,
            'message' => 'Su reserva para el espacio ' . $this->reservation->espacio->codigo . ' ha sido confirmada.',
        ];
    }
}
