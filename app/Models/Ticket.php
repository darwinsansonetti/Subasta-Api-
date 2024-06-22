<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model{
    protected $table = "ticket";

    protected $fillable = [
        'fecha_creacion', 'monto', 'activo', 'tipo_apuesta_id', 'caballo_id', 'user_id',
    ];

    // public $timestamps = false;

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        
    ];

    //Un Ticke pertenece a uno y solo un User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    //Un Ticket tiene uno y solo un Tipo de Apuesta
    public function tipo_apuesta(): BelongsTo
    {
        return $this->belongsTo(Tipo_apuesta::class);
    }

    //Un Ticket genera una y solo una Transaccion
    public function transaccion(): HasOne
    {
        return $this->hasOne(Transaccion::class);
    }
}