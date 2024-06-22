<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caballo extends Model{
    protected $table = "caballo";

    protected $fillable = [
        'name', 'nro_caballo', 'jinete', 'retirado', 'puesto_llegada', 'dividendo', 'activo', 'borrado', 'carrera_id',
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

    //Un Caballo pertenece a una y solo una Carrera
    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class);
    }

    //Un Caballo es subastado en una subasta
    public function caballo_subastado(): HasOne
    {
        return $this->hasOne(Caballo_subastado::class);
    }
}