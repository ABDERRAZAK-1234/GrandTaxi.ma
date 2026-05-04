<?php

namespace App\Events;

use App\Models\Reservation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReservationCreated implements ShouldBroadcastNow
{
    // traits
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Reservation $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('trajets.' . $this->reservation->trajet_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'reservation.created';
    }

    public function broadcastWith(): array
    {
        return [
            'reservation_id' => $this->reservation->id,
            'taxi_id' => $this->reservation->taxi_id,
            'trajet_id' => $this->reservation->trajet_id,
            'sieges' => $this->reservation->sieges,
            'nombre_place' => $this->reservation->nombre_place,
            'places_restantes' => $this->getPlacesRestantes(),
        ];
    }

    private function getPlacesRestantes(): int
    {
        $taxi = $this->reservation->taxi;
        $placesReservees = Reservation::where('taxi_id', $taxi->id)
            ->where('trajet_id', $this->reservation->trajet_id)
            ->where('statut', 'confirmed')
            ->sum('nombre_place');

        return max(0, $taxi->capacite - $placesReservees);
    }
}
