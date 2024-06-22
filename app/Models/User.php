<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;

// use Illuminate\Auth\Authenticatable;
// use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
// use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model //implements AuthenticatableContract, AuthorizableContract
{
    // use Authenticatable, Authorizable, HasFactory;
    use Authenticatable, Authorizable;

    protected $table = "user";

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name', 'email', 'cedula', 'direccion', 'fecha_nacimiento', 'phone', 'saldo', 'activo', 'username',
        'password', 'rol_id', 'api_token',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        'created_at', 'updated_at',
    ];

    //Un User tiene uno y solo un Rol
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class);
    }

    //Un User puede crear muchos Tickets
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    //Un User puede crear muchas Transacciones
    public function transaccion(): HasMany
    {
        return $this->hasMany(Transaccion::class);
    }

    //Un User puede subastar a muchos Caballos
    public function caballo_subastado(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
