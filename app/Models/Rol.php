<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model{
    protected $table = "rol";

    // protected $fillable = [];

    // public $timestamps = false;

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        'created_at', 'updated_at',
    ];

    //Un Rol pertenece a muchos Users
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}