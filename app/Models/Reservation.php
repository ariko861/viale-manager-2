<?php

namespace App\Models;

use App\Mail\FormulaireReservationLien;
use App\Mail\ReservationConfirmed;
use App\Mail\ReservationConfirmedToVisitor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Random\RandomException;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        "confirmed_at",
        "remarques_accueil",
        "remarques_visiteur",
        "link_sent",
        "authorize_edition",
        "contact_email",
        "contact_phone",
        'all_mails_required',
    ];
    protected $attributes = [
        'max_days_change' => 255,
        'max_visitors' => 5
    ];

    public function sejours(): HasMany
    {
        return $this->hasMany(Sejour::class);
    }

    protected function isConfirmed(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => !( $this->confirmed_at == null ),
        );
    }

    public function confirm(): void
    {
        $this->confirmed_at = now();
        $this->authorize_edition = false;
        $this->save();
        Mail::to(Option::getVialeEmail())->queue(new ReservationConfirmed($this));
        if ($this->contact_email){
            Mail::to($this->contact_email)->queue(new ReservationConfirmedToVisitor($this));
        }
    }

    public function generateLinkToken(): void {
        $this->link_token = Str::uuid();
    }

    public function getLink(): string {
        return urldecode(route('confirmation', $this->link_token) );// . '?link_token=' . $this->link_token);
    }

    public function getLinkConfirmed(): ?string
    {
        if (!$this->isConfirmed()){
            return null;
        }
        return urldecode(route('confirmed', $this->link_token) );
    }

    /**
     * @throws RandomException
     */
    public function getColor(): string {
        mt_srand($this->id);
        $red = mt_rand(128, 230);
        $green = mt_rand(128, 230);
        $blue = mt_rand(128, 230);
        return sprintf("#%02x%02x%02x", $red, $green, $blue);
    }

    public static function createQuickReservation(int $max_days_change = 5, int $max_visitors = 5, ?string $remarques_accueil = null): self
    {
        $newReservation = new self();
        $newReservation->generateLinkToken();
        $newReservation->max_days_change = $max_days_change;
        $newReservation->max_visitors = $max_visitors;
        $newReservation->remarques_accueil = $remarques_accueil;
        $newReservation->save();
        return $newReservation;
    }

    /**
     * Fonction pour envoyer lien de la réservation à la personne de contact
     * @return void
     */
    public function sendLink(): void
    {
        if ($this->contact_email){
            $this->authorize_edition = true;
            Mail::to($this->contact_email)->queue(new FormulaireReservationLien($this));
            $this->link_sent = true;
            $this->save();
        }
    }

    public function scopeIsConfirmed(Builder $query): void
    {
        $query->whereNotNull('confirmed_at');
    }

    protected static function booted(): void
    {
        static::creating(function (Reservation $reservation) {
            $reservation->generateLinkToken();
        });
    }
}
