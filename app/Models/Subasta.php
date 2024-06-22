<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subasta extends Model{
    protected $table = "subasta";

    protected $fillable = [
        'total', 'porcentaje', 'premio', 'carrera_id', 'activa', 
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

    //Una Subasta es generada solo por una carrera
    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class);
    }

    //Una Subasta tiene muchos Caballos
    public function caballo(): HasMany
    {
        return $this->hasMany(Caballo::class);
    }
}