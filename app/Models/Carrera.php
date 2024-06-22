<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrera extends Model{
    protected $table = "carrera";

    protected $fillable = [
        'nro_carrera', 'fecha', 'distancia', 'hora', 'activa', 'confirmado', 'hipodromo_id', 'borrada',
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

    //Una Carrera pertenece a un solo Hipodromo
    public function hipodromo(): BelongsTo
    {
        return $this->belongsTo(Hipodromo::class);
    }

    //Una Carrera tiene muchos Caballos
    public function caballo(): HasMany
    {
        return $this->hasMany(Caballo::class);
    }

    //Una Carrea genera una Subasta
    public function subasta(): HasOne
    {
        return $this->hasOne(Subasta::class);
    }
}