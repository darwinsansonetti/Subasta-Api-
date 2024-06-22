<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hipodromo extends Model{
    protected $table = "hipodromo";

    protected $fillable = [
        'name',
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

    //Un Hipodromo tiene muchas Carreras
    public function carrera(): HasMany
    {
        return $this->hasMany(Carrera::class);
    }

    //Un Hipodromo puede tener muchos tipos de Jugadas
    public function jugadas(): HasMany
    {
        return $this->hasMany(Jugadas::class);
    }
}