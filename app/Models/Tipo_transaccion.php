<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tipo_transaccion extends Model{
    protected $table = "tipo_transaccion";

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

    //Un Tipo de transaccion pertenece a muchas Transacciones
    public function transaccion(): HasMany
    {
        return $this->hasMany(Transaccion::class);
    }
}