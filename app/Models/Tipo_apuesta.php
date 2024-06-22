<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tipo_apuesta extends Model{
    protected $table = "tipo_apuesta";

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

    //Un Tipo de Apuesta pertenece a muchos Ticket
    public function ticket(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    //Un Tipo de apuesta puede tener muchos tipos de Jugadas
    public function jugadas(): HasMany
    {
        return $this->hasMany(Jugadas::class);
    }
}