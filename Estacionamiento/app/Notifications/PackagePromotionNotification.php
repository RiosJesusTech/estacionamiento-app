<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PackagePromotionNotification extends Notification
{
    use Queueable;

    public $package;
    public $message;

    /**
     * Create a new notification instance.
     */
    public function __construct($package, $message)
    {
        $this->package = $package;
        $this->message = $message;
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
            ->subject('Promoción especial de paquete de estacionamiento')
            ->line($this->message)
            ->line('Paquete: ' . $this->package->name)
            ->line('Descripción: ' . $this->package->description)
            ->line('Precio: $' . $this->package->price)
            ->action('Ver paquetes', url('/packages'))
            ->line('Gracias por ser un cliente frecuente.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'package_name' => $this->package->name,
            'package_price' => $this->package->price,
            'message' => $this->message,
        ];
    }
}
