<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jugadas extends Model{
    protected $table = "jugadas";

    protected $fillable = [
        'name', 'hipodromo_id', 'tipo_apuesta_id', 'activa'
    ];

    // public $timestamps = false;

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        'created_at', 'updated_at',        
    ];

    //Una registro de Jugada pertenece a uno y solo un Hipodromo
    public function hipodromo(): BelongsTo
    {
        return $this->belongsTo(Hipodromo::class);
    }

    //Una registro de Jugada pertenece a uno y solo un Tipo de Apuesta
    public function tipo_apuesta(): BelongsTo
    {
        return $this->belongsTo(Tipo_apuesta::class);
    }
}