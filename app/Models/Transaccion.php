<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaccion extends Model{
    protected $table = "transaccion";

    protected $fillable = [
        'monto', 'tipo_transaccion_id', 'pivot_id_jugada',
    ];

    // public $timestamps = false;

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        
    ];

    //Una Transaccion tiene uno y solo un Tipo de Transaccion
    public function tipo_transaccion(): BelongsTo
    {
        return $this->belongsTo(Tipo_transaccion::class);
    }

    //Una Transaccion pertenece a uno y solo un User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    //Una Transaccion es generada por uno y solo un Ticket
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}