<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UserInvite extends Model
{
    use HasFactory;

    protected $fillable = [
        'email', 'invitation_token', 'registered_at',
    ];

    public function generateInvitationToken() {
        $this->invitation_token = Str::uuid();
    }

    public function getLink() {
        return urldecode(route('register') . '?invitation_token=' . $this->invitation_token);
    }
}
