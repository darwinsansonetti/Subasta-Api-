<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caballo_subastado extends Model{
    protected $table = "caballo_subastado";

    protected $fillable = [
        'monto_subastado', 'subasta_id', 'caballo_id', 'puesto_llegada', 'user_id', 'borrado',
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

    //Un Caballo subastado pertenece a una y solo una Subasta
    public function subasta(): BelongsTo
    {
        return $this->belongsTo(Subasta::class);
    }

    //Los Datos aca pertenecen a un solo caballo
    public function caballo(): BelongsTo
    {
        return $this->belongsTo(Caballo::class);
    }

    //Un Caballo es Subastado por uno y solo un User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}